<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GoogleSheetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserLibraryController extends Controller
{
    public function __construct(protected GoogleSheetService $sheets)
    {
    }

    public function favorites(Request $request)
    {
        $favorites = $this->sheets->getFavorites($request->user()->id);
        $movies = $this->sheets->getMovies(null);
        $indexed = collect($movies)->keyBy('id');

        $data = array_map(function ($fav) use ($indexed) {
            $fav['movie'] = $indexed->get((string) $fav['movie_id']);

            return $fav;
        }, $favorites);

        return response()->json(['data' => $data]);
    }

    public function addFavorite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'movie_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $fav = $this->sheets->addFavorite($request->user()->id, $request->input('movie_id'));

        return response()->json(['data' => $fav, 'message' => 'Added to favorites.'], 201);
    }

    public function removeFavorite(Request $request, string $movieId)
    {
        $this->sheets->removeFavorite($request->user()->id, $movieId);

        return response()->json(['message' => 'Removed from favorites.']);
    }

    public function watchHistory(Request $request)
    {
        $history = $this->sheets->getWatchHistory($request->user()->id);
        $movies = $this->sheets->getMovies(null);
        $indexed = collect($movies)->keyBy('id');

        usort($history, fn ($a, $b) => strcmp((string) ($b['watched_at'] ?? ''), (string) ($a['watched_at'] ?? '')));

        $data = array_map(function ($item) use ($indexed) {
            $item['movie'] = $indexed->get((string) $item['movie_id']);

            return $item;
        }, $history);

        return response()->json(['data' => $data]);
    }

    public function saveProgress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'movie_id' => 'required',
            'progress' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $entry = $this->sheets->upsertWatchHistory(
            $request->user()->id,
            $request->input('movie_id'),
            $request->input('progress')
        );

        return response()->json(['data' => $entry, 'message' => 'Progress saved.']);
    }
}
