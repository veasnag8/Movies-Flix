<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GoogleDriveService;
use App\Services\GoogleSheetService;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function __construct(
        protected GoogleSheetService $sheets,
        protected GoogleDriveService $drive
    ) {
    }

    public function index(Request $request)
    {
        try {
            $movies = $this->sheets->getMovies();

            if ($category = $request->query('category')) {
                $movies = array_values(array_filter(
                    $movies,
                    fn ($m) => strcasecmp((string) ($m['category'] ?? ''), $category) === 0
                ));
            }

            return response()->json([
                'data' => $movies,
                'meta' => [
                    'trending' => array_slice($this->sortByRating($movies), 0, 12),
                    'latest' => array_slice($this->sortByDate($movies), 0, 12),
                    'recommended' => array_slice($this->sortByRating($movies), 0, 8),
                    'hero' => $this->pickHero($movies),
                ],
            ]);
        } catch (\Throwable $e) {
            return $this->googleError($e);
        }
    }

    public function show(string $id)
    {
        try {
            $movie = is_numeric($id)
                ? $this->sheets->getMovieById($id)
                : $this->sheets->getMovieBySlug($id);

            if (! $movie) {
                return response()->json(['message' => 'Movie not found.'], 404);
            }

            $movie['stream_url'] = ! empty($movie['drive_video_id'])
                ? $this->drive->getStreamingUrl($movie['drive_video_id'])
                : null;
            $movie['embed_url'] = ! empty($movie['drive_video_id'])
                ? $this->drive->getEmbedUrl($movie['drive_video_id'])
                : null;

            $related = array_values(array_filter(
                $this->sheets->getMovies(),
                fn ($m) => ($m['category'] ?? '') === ($movie['category'] ?? '')
                    && (string) $m['id'] !== (string) $movie['id']
            ));

            return response()->json([
                'data' => $movie,
                'related' => array_slice($related, 0, 8),
            ]);
        } catch (\Throwable $e) {
            return $this->googleError($e);
        }
    }

    public function search(Request $request)
    {
        try {
            $q = (string) $request->query('q', $request->query('query', ''));

            return response()->json([
                'data' => $this->sheets->searchMovies($q),
                'query' => $q,
            ]);
        } catch (\Throwable $e) {
            return $this->googleError($e);
        }
    }

    public function categories()
    {
        try {
            return response()->json([
                'data' => $this->sheets->getCategories(),
            ]);
        } catch (\Throwable $e) {
            return $this->googleError($e);
        }
    }

    protected function sortByRating(array $movies): array
    {
        usort($movies, fn ($a, $b) => (float) ($b['rating'] ?? 0) <=> (float) ($a['rating'] ?? 0));

        return $movies;
    }

    protected function sortByDate(array $movies): array
    {
        usort($movies, fn ($a, $b) => strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? '')));

        return $movies;
    }

    protected function pickHero(array $movies): ?array
    {
        if ($movies === []) {
            return null;
        }

        $withBanner = array_values(array_filter($movies, fn ($m) => ! empty($m['banner_url'])));

        return ($withBanner ?: $this->sortByRating($movies))[0] ?? null;
    }

    protected function googleError(\Throwable $e)
    {
        report($e);

        return response()->json([
            'message' => 'Unable to reach Google Sheets. Check API credentials.',
            'error' => $e->getMessage(),
            'data' => [],
        ], 503);
    }
}
