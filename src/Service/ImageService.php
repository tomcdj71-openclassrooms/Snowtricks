<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ImageService.
 *
 * @see ImageServiceInterface
 *
 * @method string add(UploadedFile $image, string $type, ?int $width, ?int $height, ?string $folder = ''): string
 * @method bool delete(string $file, string $type): bool
 */
class ImageService implements ImageServiceInterface
{
    public const IMAGE_DIRECTORY = 'assets';
    public const DEFAULT_FILE = 'default/default-avatar.png';
    public const USER_AVATARS_DIRECTORY = 'assets/uploads/users/avatars';
    public const TRICK_IMAGES_DIRECTORY = 'assets/uploads/tricks/images';

    /**
     * ImageService constructor.
     */
    public function __construct(private TranslatorInterface $translator)
    {
        $this->translator = $translator;
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
    public function add(UploadedFile $image, string $type, ?int $width, ?int $height, ?string $folder = ''): string
    {
        $width = (int) $width;
        $height = (int) $height;
        $file = md5(uniqid((string) rand(), true)).'.webp';
        $imageInfos = getimagesize($image);
        if (false === $imageInfos) {
            throw new \Exception($this->translator->trans('Could not get image size. Please check the image format.'));
        }
        $image_source = $this->getImageResource($image, $imageInfos['mime']);
        if (false === $image_source) {
            throw new \Exception($this->translator->trans('Failed to create an image resource from file.'));
        }
        $image_destination = imagecreatetruecolor($width, $height);
        if (false === $image_destination) {
            throw new \Exception($this->translator->trans('Failed to create a true color image.'));
        }
        imagecopyresampled($image_destination, $image_source, 0, 0, 0, 0, $width, $height, $imageInfos[0], $imageInfos[1]);
        $path = $this->getImageDirectory($type).($folder ? '/'.$folder : '');
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        if ('tricks' === $type) {
            $miniDir = $path.'/mini';
            if (!file_exists($miniDir)) {
                mkdir($miniDir, 0777, true);
            }
            $mini = $miniDir.'/'.$file;
            $image_destination_mini = imagecreatetruecolor(300, 300);
            if (false === $image_destination_mini) {
                throw new \Exception($this->translator->trans('Failed to create a mini true color image.'));
            }
            imagecopyresampled($image_destination_mini, $image_source, 0, 0, 0, 0, 300, 300, $imageInfos[0], $imageInfos[1]);
            if (!imagepalettetotruecolor($image_destination_mini)) {
                throw new \Exception($this->translator->trans('Failed to convert palette to true color.'));
            }
            if (!imagewebp($image_destination_mini, $mini)) {
                throw new \Exception($this->translator->trans('Failed to output a WebP image.'));
            }
        }
        $original = $path.'/'.$file;
        if (!imageistruecolor($image_source) && !imagepalettetotruecolor($image_source)) {
            throw new \Exception($this->translator->trans('Failed to convert source image palette to true color.'));
        }
        if (!imagewebp($image_source, $original)) {
            throw new \Exception($this->translator->trans('Failed to output a source WebP image.'));
        }

        return $file;
    }

    public function delete(string $file, string $type): bool
    {
        if (self::DEFAULT_FILE !== $file) {
            $success = false;
            $path = $this->getImageDirectory($type);
            $mini = $path.'/mini/'.$file;
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

    private function getImageDirectory(string $type): string
    {
        switch ($type) {
            case 'avatars':
                $directory = self::USER_AVATARS_DIRECTORY;
                break;
            case 'tricks':
                $directory = self::TRICK_IMAGES_DIRECTORY;
                break;
            default:
                throw new \InvalidArgumentException($this->translator->trans('Invalid type: '.$type));
        }

        return $directory;
    }

    public function addTrickImage(UploadedFile $image, string $type, ?int $width = 1920, ?int $height = 1080, ?string $folder = ''): string
    {
        return $this->add($image, 'tricks', $width, $height, $folder);
    }

    public function deleteTrickImage(string $file, string $type): bool
    {
        return $this->delete($file, 'tricks');
    }

    public function addUserAvatar(UploadedFile $avatar, string $type, ?int $width, ?int $height, ?string $folder = ''): string
    {
        return $this->add($avatar, 'avatars', $width, $height, $folder);
    }

    public function deleteUserAvatar(string $file, string $type): bool
    {
        return $this->delete($file, 'avatars');
    }
}
