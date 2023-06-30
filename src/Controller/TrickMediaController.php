<?php

namespace App\Controller;

use App\Handler\TrickHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/delete-media/')]
class TrickMediaController extends AbstractController
{
    public function __construct(private TrickHandler $trickHandler, private TranslatorInterface $translator)
    {
        $this->trickHandler = $trickHandler;
        $this->translator = $translator;
    }

    #[Route('/image/{id}', name: 'delete_image', methods: ['DELETE'])]
    public function deleteImage(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
        $imageId = $request->attributes->get('id');
        $imageRepo = $this->trickHandler->getEntityManager()->getRepository(\App\Entity\Image::class);
        $image = $imageRepo->findOneBy(['id' => $imageId]);

        if (!$image instanceof \App\Entity\Image) {
            return new JsonResponse(['error' => $this->translator->trans('Image not found')], 404);
        }

        $content = json_decode($request->getContent(), true);
        if (!is_array($content) || !isset($content['_csrf'])) {
            return new JsonResponse(['error' => $this->translator->trans('Invalid request data')], 400);
        }
        $token = (string) $content['_csrf'];
        if ($this->isCsrfTokenValid('delete'.$imageId, $token)) {
            $name = $image->getPath();
            if (null !== $name && $this->trickHandler->delete($name, 'tricks/images', 300, 300)) {
                $trick = $image->getTrick();
                if ($trick instanceof \App\Entity\Trick && $trick->getFeaturedImage() instanceof \App\Entity\Image) {
                    if ($image === $trick->getFeaturedImage()) {
                        $trick->setFeaturedImage(null);
                    }
                    $this->trickHandler->save($trick);
                    $imageRepo->remove($image);

                    return new JsonResponse(['success' => true], 200);
                }

                return new JsonResponse(['success' => true], 200);
            }

            return new JsonResponse(['error' => $this->translator->trans('Error encountered during the deletion of the image')], 400);
        }

        return new JsonResponse(['error' => $this->translator->trans('Invalid token')], 400);
    }

    #[Route('/video/{id}', name: 'delete_video', methods: ['DELETE'])]
    public function deleteVideo(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
        $videoId = $request->attributes->get('id');
        $videoRepo = $this->trickHandler->getEntityManager()->getRepository(\App\Entity\Video::class);
        $video = $videoRepo->findOneBy(['id' => $videoId]);

        if (!$video instanceof \App\Entity\Video) {
            return new JsonResponse(['error' => $this->translator->trans('Video not found')], 404);
        }

        $content = json_decode($request->getContent(), true);
        if (!is_array($content) || !isset($content['_csrf'])) {
            return new JsonResponse(['error' => $this->translator->trans('Invalid request data')], 400);
        }
        $token = (string) $content['_csrf'];
        if ($this->isCsrfTokenValid('delete'.$videoId, $token)) {
            $trick = $video->getTrick();
            if ($trick instanceof \App\Entity\Trick) {
                $trick->removeVideo($video);
                $this->trickHandler->save($trick);
                $videoRepo->remove($video);

                return new JsonResponse(['success' => true], 200);
            }

            return new JsonResponse(['error' => $this->translator->trans('Error encountered during the deletion of the video')], 400);
        }

        return new JsonResponse(['error' => $this->translator->trans('Invalid token')], 400);
    }
}
