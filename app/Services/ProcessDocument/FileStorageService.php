<?php

namespace App\Services\ProcessDocument;

use ErrorException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FileStorageService
{
    public function fileStorage($file, Model $model): array
    {
        $folderPath = $this->folderPath($model);

        Storage::disk('public')->makeDirectory($folderPath);

        // Parse the base64 image
        $image_parts = explode(';base64,', $file);
        if (count($image_parts) < 2) {
            throw new ErrorException('Invalid base64 format');
        }

        $image_parts_ends = explode(',', $file);
        $image_type = $this->getFileExtensionBase64($file);
        $image_base64 = base64_decode($image_parts[1]);

        $name = uniqid();
        $fileName = $name.'.'.$image_type;
        $filePath = $folderPath.$fileName;

        Storage::disk('public')->put($filePath, $image_base64);

        return [
            'type' => $image_type,
            'storage' => $filePath,
            'image_type' => $image_type,
            'name' => $name,
            'base64_file' => $file,
            'base64_type' => $image_parts_ends[0].',',
        ];
    }

    public function folderPath($model): string
    {
        if (! $model) {
            throw new ErrorException('Sorry!!! Model Error');
        }

        $baseFolder = rtrim(config('upload.folder', 'user'), '/');
        $modelName = strtolower(class_basename($model));

        $directory = "{$baseFolder}/{$modelName}/{$model->id}/";

        return $directory;
    }

    public function folderPathWithoutModel(): string
    {
        $baseFolder = rtrim(config('upload.folder', 'user'), '/');

        return "{$baseFolder}/random/";
    }

    public function getFileExtensionBase64($file): string
    {
        $mimeType = mime_content_type($file);
        if (! $mimeType) {
            throw new ErrorException('Sorry!!! Extension not found');
        }

        $parts = explode('/', $mimeType);

        return $parts[1] ?? throw new ErrorException('Invalid mime type');
    }

    public function findFileExtension($file): string
    {
        return $file ? $file->getClientOriginalExtension() : throw new ErrorException('Original extension not found');
    }
}
