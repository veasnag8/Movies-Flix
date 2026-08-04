<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\GoogleDriveService;
use App\Services\GoogleSheetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MovieAdminController extends Controller
{
    public function __construct(
        protected GoogleSheetService $sheets,
        protected GoogleDriveService $drive
    ) {
    }

    public function index()
    {
        return response()->json([
            'data' => $this->sheets->getAllMovies(),
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'poster_url' => 'nullable|string',
            'banner_url' => 'nullable|string',
            'category' => 'nullable|string|max:120',
            'year' => 'nullable|string|max:10',
            'duration' => 'nullable|string|max:40',
            'rating' => 'nullable|string|max:10',
            'language' => 'nullable|string|max:60',
            'quality' => 'nullable|string|max:40',
            'drive_video_id' => 'nullable|string|max:120',
            'trailer_url' => 'nullable|string',
            'subtitle_url' => 'nullable|string',
            'status' => 'nullable|in:active,inactive,draft',
            'video' => 'nullable|file|max:524288',
            'poster' => 'nullable|image|max:10240',
            'banner' => 'nullable|image|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);

        try {
            if ($request->hasFile('video')) {
                $uploaded = $this->drive->uploadMovie($request->file('video'), $data['slug'].'.mp4');
                $data['drive_video_id'] = $uploaded['id'];
            }

            if ($request->hasFile('poster')) {
                $poster = $this->drive->uploadPoster($request->file('poster'), $data['slug'].'-poster');
                $data['poster_url'] = $poster['url'];
            }

            if ($request->hasFile('banner')) {
                $banner = $this->drive->uploadPoster($request->file('banner'), $data['slug'].'-banner');
                $data['banner_url'] = $banner['url'];
            }

            $movie = $this->sheets->createMovie($data);

            return response()->json(['data' => $movie, 'message' => 'Movie created.'], 201);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Failed to create movie.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'poster_url' => 'nullable|string',
            'banner_url' => 'nullable|string',
            'category' => 'nullable|string|max:120',
            'year' => 'nullable|string|max:10',
            'duration' => 'nullable|string|max:40',
            'rating' => 'nullable|string|max:10',
            'language' => 'nullable|string|max:60',
            'quality' => 'nullable|string|max:40',
            'drive_video_id' => 'nullable|string|max:120',
            'trailer_url' => 'nullable|string',
            'subtitle_url' => 'nullable|string',
            'status' => 'nullable|in:active,inactive,draft',
            'video' => 'nullable|file|max:524288',
            'poster' => 'nullable|image|max:10240',
            'banner' => 'nullable|image|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        try {
            $existing = $this->sheets->getMovieById($id);
            if (! $existing) {
                return response()->json(['message' => 'Movie not found.'], 404);
            }

            $slug = $data['slug'] ?? $existing['slug'] ?? Str::slug($data['title'] ?? $existing['title']);

            if ($request->hasFile('video')) {
                if (! empty($existing['drive_video_id'])) {
                    $this->drive->deleteFile($existing['drive_video_id']);
                }
                $uploaded = $this->drive->uploadMovie($request->file('video'), $slug.'.mp4');
                $data['drive_video_id'] = $uploaded['id'];
            }

            if ($request->hasFile('poster')) {
                $poster = $this->drive->uploadPoster($request->file('poster'), $slug.'-poster');
                $data['poster_url'] = $poster['url'];
            }

            if ($request->hasFile('banner')) {
                $banner = $this->drive->uploadPoster($request->file('banner'), $slug.'-banner');
                $data['banner_url'] = $banner['url'];
            }

            $movie = $this->sheets->updateMovie($id, $data);

            return response()->json(['data' => $movie, 'message' => 'Movie updated.']);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Failed to update movie.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $movie = $this->sheets->getMovieById($id);
            if (! $movie) {
                return response()->json(['message' => 'Movie not found.'], 404);
            }

            if (! empty($movie['drive_video_id'])) {
                $this->drive->deleteFile($movie['drive_video_id']);
            }

            $this->sheets->deleteMovie($id);

            return response()->json(['message' => 'Movie deleted.']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to delete movie.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function uploadVideo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'video' => 'required|file|max:524288',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $uploaded = $this->drive->uploadMovie($request->file('video'));

            return response()->json(['data' => $uploaded]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Video upload failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function uploadPoster(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'poster' => 'required|image|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $uploaded = $this->drive->uploadPoster($request->file('poster'));

            return response()->json(['data' => $uploaded]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Poster upload failed.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
