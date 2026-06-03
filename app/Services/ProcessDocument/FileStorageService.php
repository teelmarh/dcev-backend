<?php

namespace App\Services\ProcessDocument;

use ErrorException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;

class FileStorageService
{
    /**
     * Store a multipart uploaded file on the public disk.
     * Returns the relative file path.
     */
    public function storeUploadedFile(UploadedFile $file, Model $model): string
    {
        $folder   = $this->folderPath($model);
        $fileName = uniqid() . '.' . $file->getClientOriginalExtension();

        Storage::disk('public')->makeDirectory($folder);
        Storage::disk('public')->putFileAs($folder, $file, $fileName);

        return $folder . $fileName;
    }

    /**
     * Store a base64 data URI on the public disk.
     * Returns the relative file path.
     */
    public function storeBase64File(string $base64, Model $model): string
    {
        // Parse: data:image/jpeg;base64,<data>
        if (! str_contains($base64, ';base64,')) {
            throw new ErrorException('Invalid base64 format — missing data URI header.');
        }

        [$header, $data] = explode(';base64,', $base64, 2);

        // Extract extension from header (e.g. "data:image/jpeg" → "jpeg")
        $mimeType  = str_replace('data:', '', $header);
        $extension = $this->extensionFromMime($mimeType);

        $decoded = base64_decode($data, strict: true);
        if ($decoded === false) {
            throw new ErrorException('Failed to decode base64 image data.');
        }

        $folder   = $this->folderPath($model);
        $fileName = uniqid() . '.' . $extension;
        $filePath = $folder . $fileName;

        Storage::disk('public')->makeDirectory($folder);
        Storage::disk('public')->put($filePath, $decoded);

        return $filePath;
    }

    public function folderPath(Model $model): string
    {
        $base      = rtrim(config('filesystems.photo_folder', 'photos'), '/');
        $modelName = strtolower(class_basename($model));

        return "{$base}/{$modelName}/{$model->id}/";
    }

    private function extensionFromMime(string $mimeType): string
    {
        return match ($mimeType) {
            'image/jpeg'    => 'jpg',
            'image/png'     => 'png',
            'image/gif'     => 'gif',
            'image/webp'    => 'webp',
            'image/bmp'     => 'bmp',
            default         => throw new ErrorException("Unsupported image type: {$mimeType}"),
        };
    }
}

