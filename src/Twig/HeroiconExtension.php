<?php 

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class HeroiconExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('heroicon', [$this, 'getHeroicon']),
        ];
    }

    public function getHeroicon(string $iconName,  string $class = ''): string
    {
        $iconPath = __DIR__ . "/../../assets/heroicons/{$iconName}.svg";

        if (!file_exists($iconPath)) {
            throw new \RuntimeException("Icon not found: {$iconName}");
        }

        $svgContent = file_get_contents($iconPath);

        $svgContent = str_replace('<svg', sprintf('<svg class="%s"', $class), $svgContent);

        return $svgContent;
    }
}
