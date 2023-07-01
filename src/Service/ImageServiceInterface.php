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
    public function add(UploadedFile $image, string $type, ?int $width, ?int $height, ?string $folder = ''): string;

    /**
     * Delete an image and its associated thumbnail.
     */
    public function delete(string $file, string $type): bool;

    /**
     * Add a trick image and generate a thumbnail.
     */
    public function addTrickImage(UploadedFile $image, string $type, ?int $width = 1920, ?int $height = 1080, ?string $folder = ''): string;

    /**
     * Delete a trick image and its associated thumbnail.
     */
    public function deleteTrickImage(string $file, string $type): bool;

    /**
     * Add a user avatar and generate a thumbnail.
     */
    public function addUserAvatar(UploadedFile $avatar, string $type, ?int $width, ?int $height, ?string $folder = ''): string;

    /**
     * Delete a user avatar and its associated thumbnail.
     */
    public function deleteUserAvatar(string $file, string $type): bool;
}
