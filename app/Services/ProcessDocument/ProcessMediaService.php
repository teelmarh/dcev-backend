<?php

namespace App\Services\ProcessDocument;

use Illuminate\Http\UploadedFile;

class ProcessMediaService
{
    /**
     * Process a profile photo upload and return the stored file path.
     * Accepts either an UploadedFile (multipart/form-data) or a base64 data URI string.
     *
     * @param  UploadedFile|string|null  $image
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return string|null  Relative storage path (public disk)
     */
    public function processImage(mixed $image, $model): ?string
    {
        if (! $image) {
            return null;
        }

        $storageService = new FileStorageService();

        if ($image instanceof UploadedFile) {
            return $storageService->storeUploadedFile($image, $model);
        }

        // Treat as base64 data URI
        return $storageService->storeBase64File($image, $model);
    }
}
