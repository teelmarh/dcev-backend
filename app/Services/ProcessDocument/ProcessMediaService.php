<?php

namespace App\Services\ProcessDocument;

class ProcessMediaService
{
    public function processImage(?string $photoBase64, $model)
    {
        if (! $photoBase64) {
            return null;
        }

        if ($model->relationLoaded('media') || $model->media()->exists()) {
            foreach ($model->media as $media) {
                if (file_exists(public_path($media->file_path))) {
                    unlink(public_path($media->file_path));
                }
                $media->delete();
            }
        }

        $storeImageValue = (new FileStorageService)->fileStorage($photoBase64, $model);

        $model->media ? $model->media()->create([
            'file_path' => $storeImageValue['storage'],
        ]) : null;

        return $storeImageValue['storage'];
    }
}
