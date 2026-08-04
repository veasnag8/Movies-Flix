<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Local file storage stand-in for Google Drive in demo mode.
 */
class LocalDriveService extends GoogleDriveService
{
    public function __construct()
    {
        //
    }

    public function upload(UploadedFile|string $file, ?string $name = null, ?string $folderId = null, ?string $mimeType = null): array
    {
        if ($file instanceof UploadedFile) {
            $name = $name ?: $file->getClientOriginalName();
            $stored = $file->storeAs('uploads', Str::uuid().'_'.$name, 'public');
        } else {
            $name = $name ?: basename($file);
            $stored = 'uploads/'.Str::uuid().'_'.$name;
            Storage::disk('public')->put($stored, file_get_contents($file));
        }

        $id = 'local_'.Str::random(16);

        return [
            'id' => $id,
            'name' => $name,
            'mime_type' => $mimeType,
            'size' => null,
            'url' => Storage::disk('public')->url($stored),
            'view_url' => Storage::disk('public')->url($stored),
            'preview_url' => Storage::disk('public')->url($stored),
            'path' => $stored,
        ];
    }

    public function deleteFile(string $fileId): bool
    {
        return true;
    }

    public function getStreamingUrl(string $fileId): string
    {
        if (str_starts_with($fileId, 'DEMO_') || str_starts_with($fileId, 'local_')) {
            return 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4';
        }

        return 'https://drive.google.com/uc?id='.$fileId.'&export=download';
    }

    public function getEmbedUrl(string $fileId): string
    {
        if (str_starts_with($fileId, 'DEMO_') || str_starts_with($fileId, 'local_')) {
            return 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4';
        }

        return 'https://drive.google.com/file/d/'.$fileId.'/preview';
    }

    public function makePublic(string $fileId): void
    {
        //
    }
}
