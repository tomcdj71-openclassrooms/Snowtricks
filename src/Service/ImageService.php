<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ImageService.
 *
 * @see ImageServiceInterface
 *
 * @method string add(UploadedFile $image, ?string $folder = '', int $width = 250, int $height = 250): string
 * @method bool delete(string $file, ?string $folder = '', int $width = 250, int $height = 250): bool
 */
class ImageService implements ImageServiceInterface
{
    public const IMAGE_DIRECTORY = 'images_directory';
    public const DEFAULT_FILE = 'default.webp';

    /**
     * ImageService constructor.
     */
    public function __construct(private ParameterBagInterface $params, private TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->params = $params;
    }

    /**
     * Create a GD image resource from a given image file based on mime type.
     *
     * @throws \Exception
     */
    private function getImageResource(string $image, string $mime): \GdImage|false
    {
        switch ($mime) {
            case 'image/png':
                return imagecreatefrompng($image);
            case 'image/jpeg':
                return imagecreatefromjpeg($image);
            case 'image/webp':
                return imagecreatefromwebp($image);
            default:
                throw new \Exception($this->translator->trans('Expected image formats: png, jpeg, webp. Got: '.$mime));
        }
    }

    /**
     * Add an image and generate a thumbnail.
     *
     * @throws \Exception
     */
    public function add(UploadedFile $image, ?string $folder = '', int $width = 250, int $height = 250): string
    {
        $file = md5(uniqid((string) rand(), true)).'.webp';
        $imageInfos = getimagesize($image);
        if (false === $imageInfos) {
            throw new \Exception($this->translator->trans('Could not get image size. Please check the image format.'));
        }
        $image_source = $this->getImageResource($image, $imageInfos['mime']);
        if (false !== $image_source) {
            $image_destination = imagecreatetruecolor($width, $height);
            if (false !== $image_destination) {
                imagecopyresampled($image_destination, $image_source, 0, 0, 0, 0, $width, $height, $imageInfos[0], $imageInfos[1]);
            }
            $path = $this->getImageDirectory().($folder ?? '');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            $miniDir = $path.'/mini';
            if (!file_exists($miniDir)) {
                mkdir($miniDir, 0777, true);
            }
            $mini = $miniDir.'/'.$width.'x'.$height.'-'.$file;
            if (false !== $image_destination) {
                imagepalettetotruecolor($image_destination);
                imagewebp($image_destination, $mini);
            }
            $original = $path.'/'.$file;
            if (false !== $image_source) {
                imagewebp($image_source, $original);
            }
        }

        return $file;
    }

    /**
     * Delete an image and its associated thumbnail.
     */
    public function delete(string $file, ?string $folder = '', int $width = 250, int $height = 250): bool
    {
        if (self::DEFAULT_FILE !== $file) {
            $success = false;
            $path = $this->getImageDirectory().($folder ?? '');
            $mini = $path.'/mini/'.$width.'x'.$height.'-'.$file;
            if (file_exists($mini)) {
                unlink($mini);
                $success = true;
            }
            $original = $path.'/'.$file;
            if (file_exists($original)) {
                unlink($original);
                $success = true;
            }

            return $success;
        }

        return false;
    }

    /**
     * Get the image directory from the parameters.
     *
     * @throws \InvalidArgumentException
     */
    private function getImageDirectory(): string
    {
        $directory = $this->params->get(self::IMAGE_DIRECTORY);
        if (!is_string($directory)) {
            throw new \InvalidArgumentException($this->translator->trans('Image directory path must be a string.'));
        }

        return $directory;
    }
}
