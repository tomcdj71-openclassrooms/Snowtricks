<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class VideoProviderExtension extends AbstractExtension
{
    /**
     * @var array<string, callable>
     */
    private array $handlers;

    public function __construct()
    {
        $this->handlers = [
            'youtube.com' => function (string $url): string {
                return $this->youtubeVideoUrl($url);
            },
            'vimeo.com' => function (string $url): string {
                return $this->vimeoVideoUrl($url);
            },
            'dailymotion.com' => function (string $url): string {
                return $this->dailymotionVideoUrl($url);
            },
        ];
    }

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
        foreach ($this->handlers as $provider => $handler) {
            if (false !== strpos($url, $provider)) {
                return $handler($url);
            }
        }

        return $url;
    }

    private function youtubeVideoUrl(string $url): string
    {
        $query = [];
        $urlComponents = parse_url($url);

        if (false !== $urlComponents && is_array($urlComponents)) {
            parse_str($urlComponents['query'] ?? '', $query);
        }

        if (isset($query['v']) && is_string($query['v']) && '' !== $query['v']) {
            return 'https://www.youtube.com/embed/'.$query['v'];
        }

        return $url;
    }

    private function vimeoVideoUrl(string $url): string
    {
        $urlComponents = parse_url($url);

        if (false !== $urlComponents && is_array($urlComponents)) {
            $path = explode('/', $urlComponents['path'] ?? '');
            $videoId = end($path);

            if (is_string($videoId) && '' !== $videoId) {
                return 'https://player.vimeo.com/video/'.$videoId;
            }
        }

        return $url;
    }

    private function dailymotionVideoUrl(string $url): string
    {
        $urlComponents = parse_url($url);

        if (false !== $urlComponents && is_array($urlComponents)) {
            $path = explode('/', $urlComponents['path'] ?? '');
            $videoId = end($path);

            if (is_string($videoId) && '' !== $videoId) {
                return 'https://www.dailymotion.com/embed/video/'.$videoId;
            }
        }

        return $url;
    }
}
