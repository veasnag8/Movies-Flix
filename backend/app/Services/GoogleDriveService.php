<?php

namespace App\Services;

use Google\Service\Drive\DriveFile;
use Illuminate\Http\UploadedFile;

class GoogleDriveService
{
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

        if ($file instanceof UploadedFile) {
            $name = $name ?: $file->getClientOriginalName();
            $mimeType = $mimeType ?: $file->getMimeType();
            $path = $file->getRealPath();
        } else {
            $path = $file;
            $name = $name ?: basename($path);
            $mimeType = $mimeType ?: mime_content_type($path) ?: 'application/octet-stream';
        }

        $metadata = new DriveFile([
            'name' => $name,
        ]);

        if ($folderId) {
            $metadata->setParents([$folderId]);
        }

        $created = $drive->files->create($metadata, [
            'data' => file_get_contents($path),
            'mimeType' => $mimeType,
            'uploadType' => 'multipart',
            'fields' => 'id,name,mimeType,webViewLink,webContentLink,size',
        ]);

        $this->makePublic($created->getId());

        $id = $created->getId();
        $isImage = str_starts_with((string) $created->getMimeType(), 'image/');

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
