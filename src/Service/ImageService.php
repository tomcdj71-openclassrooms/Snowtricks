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
    public const TRICK_IMAGES_DIRECTORY_MINI = 'assets/uploads/tricks/images/mini';

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
    private function getImageResource(string $image, string $mime): \GdImage
    {
        switch ($mime) {
            case 'image/png':
                $resource = imagecreatefrompng($image);
                break;
            case 'image/jpeg':
                $resource = imagecreatefromjpeg($image);
                break;
            case 'image/webp':
                $resource = imagecreatefromwebp($image);
                break;
            default:
                throw new \Exception($this->translator->trans('Expected image formats: png, jpeg, webp. Got: '.$mime));
        }
        if (false === $resource) {
            throw new \Exception($this->translator->trans('Failed to create an image resource.'));
        }

        return $resource;
    }

    /**
     * Add an image and generate a thumbnail.
     *
     * @throws \Exception
     */
    public function add(UploadedFile $image, string $type, ?int $width, ?int $height): string
    {
        $width = (int) $width;
        $height = (int) $height;
        $file = md5(uniqid((string) rand(), true)).'.webp';
        $imageInfos = $this->getImageInfos($image);
        $sourceImage = $this->getImageResource($image, $imageInfos['mime']);
        $destImage = $this->createImageDestination($width, $height);
        imagecopyresampled($destImage, $sourceImage, 0, 0, 0, 0, $width, $height, $imageInfos['width'], $imageInfos['height']);
        $path = $this->prepareImageDirectory($type);

        if ('tricks' === $type) {
            $this->handleTrickType($file, $path, $sourceImage, $imageInfos);
        }

        $this->createOriginalImage($sourceImage, $path, $file);

        return $file;
    }

    public function delete(string $file, string $type): bool
    {
        if (self::DEFAULT_FILE !== $file) {
            $path = $this->getImageDirectory($type);
            $mini = $path.'/mini/'.$file;
            $original = $path.'/'.$file;

            return $this->deleteFileIfExists($mini) || $this->deleteFileIfExists($original);
        }

        return false;
    }

    private function deleteFileIfExists(string $filePath): bool
    {
        if (file_exists($filePath)) {
            unlink($filePath);

            return true;
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

    public function addTrickImage(UploadedFile $image, ?int $width = 1920, ?int $height = 1080): string
    {
        return $this->add($image, 'tricks', $width, $height);
    }

    public function deleteTrickImage(string $file): bool
    {
        return $this->delete($file, 'tricks');
    }

    public function addUserAvatar(UploadedFile $avatar, ?int $width, ?int $height): string
    {
        return $this->add($avatar, 'avatars', $width, $height);
    }

    public function deleteUserAvatar(string $file): bool
    {
        return $this->delete($file, 'avatars');
    }

    /**
     * @return array{width: int, height: int, mime: string}
     *
     * @throws \Exception
     */
    private function getImageInfos(UploadedFile $image): array
    {
        $imageInfos = getimagesize($image);
        if (false === $imageInfos) {
            throw new \Exception($this->translator->trans('Could not get image size. Please check the image format.'));
        }

        return [
            'width' => $imageInfos[0],
            'height' => $imageInfos[1],
            'mime' => $imageInfos['mime'],
        ];
    }

    private function createImageDestination(int $width, int $height): \GdImage
    {
        $destImage = imagecreatetruecolor($width, $height);
        if (false === $destImage) {
            throw new \Exception($this->translator->trans('Failed to create a true color image.'));
        }

        return $destImage;
    }

    private function prepareImageDirectory(string $type): string
    {
        $path = $this->getImageDirectory($type);
        if ('tricks' === $type) {
            $miniDir = $path.'/mini';
            if (!file_exists($miniDir)) {
                mkdir($miniDir, 0777, true);
            }
        }
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        return $path;
    }

    /**
     * @param array{width: int, height: int, mime: string} $imageInfos
     *
     * @throws \Exception
     */
    private function handleTrickType(string $file, string $path, \GdImage $sourceImage, array $imageInfos): void
    {
        $miniDir = $path.'/mini';
        $mini = $miniDir.'/'.$file;
        $imgDestMini = $this->createImageDestination(300, 300);
        imagecopyresampled($imgDestMini, $sourceImage, 0, 0, 0, 0, 300, 300, $imageInfos['width'], $imageInfos['height']);
        $this->ensure(imagepalettetotruecolor($imgDestMini), 'Failed to convert palette to true color.');
        $this->ensure(imagewebp($imgDestMini, $mini), 'Failed to output a WebP image.');
    }

    private function createOriginalImage(\GdImage $sourceImage, string $path, string $file): void
    {
        $original = $path.'/'.$file;
        if (!imageistruecolor($sourceImage) && !imagepalettetotruecolor($sourceImage)) {
            throw new \Exception($this->translator->trans('Failed to convert source image palette to true color.'));
        }
        if (!imagewebp($sourceImage, $original)) {
            throw new \Exception($this->translator->trans('Failed to output a source WebP image.'));
        }
    }

    private function ensure(bool $condition, string $errorMessage): void
    {
        if (!$condition) {
            throw new \Exception($this->translator->trans($errorMessage));
        }
    }
}
