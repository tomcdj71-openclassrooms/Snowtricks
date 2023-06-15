<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Interface ImageServiceInterface.
 */
interface ImageServiceInterface
{
    /**
     * Add an image and generate a thumbnail.
     */
    public function add(UploadedFile $image, ?string $folder = '', int $width = 250, int $height = 250): string;

    /**
     * Delete an image and its associated thumbnail.
     */
    public function delete(string $file, ?string $folder = '', int $width = 250, int $height = 250): bool;
}
