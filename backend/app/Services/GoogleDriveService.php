<?php

namespace App\Services;

use Google\Http\MediaFileUpload;
use Google\Service\Drive\DriveFile;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class GoogleDriveService
{
    private const SMALL_FILE_BYTES = 5 * 1024 * 1024; // 5MB

    private const CHUNK_BYTES = 1 * 1024 * 1024; // 1MB chunks

    public function __construct(protected GoogleClientFactory $google)
    {
    }

    public function uploadMovie(UploadedFile|string $file, ?string $name = null, ?string $folderId = null): array
    {
        return $this->upload($file, $name, $folderId, 'video/mp4');
    }

    public function uploadPoster(UploadedFile|string $file, ?string $name = null, ?string $folderId = null): array
    {
        $mime = $file instanceof UploadedFile
            ? ($file->getMimeType() ?: 'image/jpeg')
            : 'image/jpeg';

        return $this->upload($file, $name, $folderId, $mime);
    }

    public function upload(UploadedFile|string $file, ?string $name = null, ?string $folderId = null, ?string $mimeType = null): array
    {
        $folderId = $folderId ?: config('google.drive_folder_id');
        $drive = $this->google->drive();
        $client = $drive->getClient();

        if ($file instanceof UploadedFile) {
            $name = $name ?: $file->getClientOriginalName();
            $mimeType = $mimeType ?: $file->getMimeType() ?: 'application/octet-stream';
            $path = $file->getRealPath();
        } else {
            $path = $file;
            $name = $name ?: basename($path);
            $mimeType = $mimeType ?: mime_content_type($path) ?: 'application/octet-stream';
        }

        if (! is_string($path) || ! is_readable($path)) {
            throw new RuntimeException('Upload file path is not readable.');
        }

        $size = filesize($path);
        if ($size === false) {
            throw new RuntimeException('Unable to read upload file size.');
        }

        $metadata = new DriveFile([
            'name' => $name,
        ]);

        if ($folderId) {
            $metadata->setParents([$folderId]);
        }

        // Small files: simple multipart with string body
        if ($size <= self::SMALL_FILE_BYTES) {
            $created = $drive->files->create($metadata, [
                'data' => file_get_contents($path),
                'mimeType' => $mimeType,
                'uploadType' => 'multipart',
                'fields' => 'id,name,mimeType,webViewLink,webContentLink,size',
            ]);
        } else {
            // Large files: resumable chunked upload (avoids base64_encode on resource)
            $client->setDefer(true);
            $request = $drive->files->create($metadata, [
                'fields' => 'id,name,mimeType,webViewLink,webContentLink,size',
            ]);

            $media = new MediaFileUpload(
                $client,
                $request,
                $mimeType,
                null,
                true,
                self::CHUNK_BYTES
            );
            $media->setFileSize($size);

            $status = false;
            $handle = fopen($path, 'rb');
            if ($handle === false) {
                $client->setDefer(false);
                throw new RuntimeException('Unable to open upload file.');
            }

            try {
                while (! $status && ! feof($handle)) {
                    $chunk = fread($handle, self::CHUNK_BYTES);
                    if ($chunk === false) {
                        throw new RuntimeException('Failed reading upload chunk.');
                    }
                    $status = $media->nextChunk($chunk);
                }
            } finally {
                fclose($handle);
                $client->setDefer(false);
            }

            if (! $status instanceof DriveFile) {
                throw new RuntimeException('Drive resumable upload did not return a file.');
            }

            $created = $status;
        }

        $this->makePublic($created->getId());

        $id = $created->getId();
        $isImage = str_starts_with((string) ($created->getMimeType() ?: $mimeType), 'image/');

        return [
            'id' => $id,
            'name' => $created->getName(),
            'mime_type' => $created->getMimeType(),
            'size' => $created->getSize(),
            'url' => $isImage ? $this->getThumbnailUrl($id) : $this->getStreamingUrl($id),
            'thumbnail_url' => $this->getThumbnailUrl($id),
            'view_url' => 'https://drive.google.com/file/d/'.$id.'/view',
            'preview_url' => 'https://drive.google.com/file/d/'.$id.'/preview',
        ];
    }

    public function deleteFile(string $fileId): bool
    {
        try {
            $this->google->drive()->files->delete($fileId);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function getStreamingUrl(string $fileId): string
    {
        return 'https://drive.google.com/uc?id='.$fileId.'&export=download';
    }

    /**
     * Public image URL that works in <img> tags (unlike uc?export=download).
     */
    public function getThumbnailUrl(string $fileId, int $width = 800): string
    {
        return 'https://drive.google.com/thumbnail?id='.$fileId.'&sz=w'.$width;
    }

    public function getEmbedUrl(string $fileId): string
    {
        return 'https://drive.google.com/file/d/'.$fileId.'/preview';
    }

    public function makePublic(string $fileId): void
    {
        $permission = new \Google\Service\Drive\Permission([
            'type' => 'anyone',
            'role' => 'reader',
        ]);

        $this->google->drive()->permissions->create($fileId, $permission);
    }
}
