<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * File Helper
 * 
 * Handles file uploads and storage operations.
 */
class FileHelper
{
    /**
     * Upload a file to storage.
     *
     * @param UploadedFile|null $file
     * @param string $directory
     * @param string $disk
     * @return string|null Returns the file path relative to storage/app/public or null if no file
     */
    public function upload(?UploadedFile $file, string $directory = 'customers', string $disk = 'public'): ?string
    {
        if (!$file || !$file->isValid()) {
            return null;
        }

        // Generate unique filename
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        
        // Store file
        $path = $file->storeAs($directory, $filename, $disk);

        // Return path relative to storage/app/public
        return $path;
    }

    /**
     * Delete a file from storage.
     *
     * @param string $path
     * @param string $disk
     * @return bool
     */
    public function delete(string $path, string $disk = 'public'): bool
    {
        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }

    /**
     * Upload an image file with validation.
     *
     * @param UploadedFile|null $file
     * @param string $directory
     * @param string $disk
     * @param int $maxSize Maximum size in KB
     * @return string|null Returns the file path relative to storage/app/public or null if no file
     */
    public function uploadImage(?UploadedFile $file, string $directory = 'images', string $disk = 'public', int $maxSize = 2048): ?string
    {
        if (!$file || !$file->isValid()) {
            return null;
        }

        // Validate image
        if (!$file->isValid() || !in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
            throw new \InvalidArgumentException('Invalid image file type');
        }

        // Validate size
        if ($file->getSize() > $maxSize * 1024) {
            throw new \InvalidArgumentException("Image size exceeds maximum allowed size of {$maxSize}KB");
        }

        return $this->upload($file, $directory, $disk);
    }

    /**
     * Get full URL for a stored file.
     *
     * @param string|null $path
     * @param string $disk
     * @return string|null
     */
    public function getUrl(?string $path, string $disk = 'public'): ?string
    {
        if (!$path) {
            return null;
        }

        return Storage::disk($disk)->url($path);
    }

    /**
     * Get asset URL for a stored file (for public disk).
     *
     * @param string|null $path
     * @return string|null
     */
    public function getAssetUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return asset('storage/' . $path);
    }
}
