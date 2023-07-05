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
    public function add(UploadedFile $image, string $type, ?int $width, ?int $height): string;

    /**
     * Delete an image and its associated thumbnail.
     */
    public function delete(string $file, string $type): bool;

    /**
     * Add a trick image and generate a thumbnail.
     */
    public function addTrickImage(UploadedFile $image, ?int $width = 1920, ?int $height = 1080): string;

    /**
     * Delete a trick image and its associated thumbnail.
     */
    public function deleteTrickImage(string $file): bool;

    /**
     * Add a user avatar and generate a thumbnail.
     */
    public function addUserAvatar(UploadedFile $avatar, ?int $width, ?int $height): string;

    /**
     * Delete a user avatar and its associated thumbnail.
     */
    public function deleteUserAvatar(string $file): bool;
}
