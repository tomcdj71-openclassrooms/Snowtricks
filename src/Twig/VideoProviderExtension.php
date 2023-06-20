<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class VideoProviderExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('video_url', function (string $url): string {
                return $this->videoUrlFilter($url);
            }),
        ];
    }

    public function videoUrlFilter(string $url): string
    {
        // Youtube URL
        if (false !== strpos($url, 'youtube.com')) {
            $query = [];
            $urlComponents = parse_url($url);

            if (false !== $urlComponents && is_array($urlComponents)) {
                parse_str($urlComponents['query'] ?? '', $query);
            }

            if (isset($query['v']) && is_string($query['v']) && '' !== $query['v']) {
                return 'https://www.youtube.com/embed/'.$query['v'];
            }
        }

        // Vimeo URL
        if (false !== strpos($url, 'vimeo.com')) {
            $urlComponents = parse_url($url);
            $path = $urlComponents['path'] ?? '';

            if ('' !== $path) {
                $pathComponents = explode('/', trim($path, '/'));
                $videoId = end($pathComponents);

                if (false !== $videoId) {
                    return 'https://player.vimeo.com/video/'.$videoId;
                }
            }
        }

        // Dailymotion URL
        if (false !== strpos($url, 'dailymotion.com')) {
            $urlComponents = parse_url($url);
            $path = $urlComponents['path'] ?? '';

            if ('' !== $path) {
                $pathComponents = explode('/', trim($path, '/'));
                $videoId = end($pathComponents);

                if (false !== $videoId) {
                    return 'https://www.dailymotion.com/embed/video/'.$videoId;
                }
            }
        }

        return $url;
    }
}
