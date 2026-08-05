<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GoogleClientFactory;
use App\Services\GoogleDriveService;
use App\Services\GoogleSheetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StreamController extends Controller
{
    public function __construct(
        protected GoogleSheetService $sheets,
        protected GoogleDriveService $drive,
        protected GoogleClientFactory $google
    ) {
    }

    public function show(Request $request, string $id): StreamedResponse|\Illuminate\Http\JsonResponse
    {
        $movie = is_numeric($id)
            ? $this->sheets->getMovieById($id)
            : $this->sheets->getMovieBySlug($id);

        if (! $movie || empty($movie['drive_video_id'])) {
            return response()->json(['message' => 'Video not found.'], 404);
        }

        $fileId = $this->drive->extractFileId((string) $movie['drive_video_id']);
        $accessToken = $this->google->accessToken();

        if ($accessToken === '') {
            return response()->json(['message' => 'Video stream unavailable.'], 503);
        }

        $url = sprintf(
            'https://www.googleapis.com/drive/v3/files/%s?alt=media&supportsAllDrives=true',
            urlencode($fileId)
        );

        $headers = [
            'Authorization' => 'Bearer '.$accessToken,
            'Accept' => '*/*',
        ];

        if ($request->header('Range')) {
            $headers['Range'] = $request->header('Range');
        }

        $response = Http::withHeaders($headers)
            ->withOptions(['stream' => true])
            ->timeout(120)
            ->get($url);

        if (! $response->successful() && $response->status() !== 206) {
            return response()->json([
                'message' => 'Video stream unavailable.',
                'error' => $response->body(),
            ], $response->status() ?: 502);
        }

        $contentType = $response->header('Content-Type', 'video/mp4');
        $contentLength = $response->header('Content-Length');
        $contentRange = $response->header('Content-Range');
        $acceptRanges = $response->header('Accept-Ranges', 'bytes');

        return response()->stream(function () use ($response) {
            $body = $response->toPsrResponse()->getBody();
            while (! $body->eof()) {
                echo $body->read(1024 * 1024);
                if (function_exists('ob_get_level') && ob_get_level() > 0) {
                    @ob_flush();
                }
                flush();
            }
        }, $response->status(), array_filter([
            'Content-Type' => $contentType,
            'Content-Length' => $contentLength,
            'Content-Range' => $contentRange,
            'Accept-Ranges' => $acceptRanges,
            'Cache-Control' => 'no-store',
        ]));
    }
}
