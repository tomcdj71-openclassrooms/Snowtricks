<?php

namespace App\Twig;

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class HeroiconExtension extends AbstractExtension
{
    public function __construct(private TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('heroicon', [$this, 'getHeroicon'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param array<string, string> $options
     *
     * @throws \RuntimeException
     */
    public function getHeroicon(string $iconName, string $class = '', string $iconType = 'solid', array $options = []): string
    {
        $iconPath = __DIR__."/../../node_modules/heroicons/24/{$iconType}/{$iconName}.svg";
        if (!file_exists($iconPath)) {
            throw new \RuntimeException($this->translator->trans("Icon not found: {$iconName}"));
        }
        $svgContent = file_get_contents($iconPath);
        if (false === $svgContent) {
            throw new \RuntimeException($this->translator->trans("Unable to read the contents of the icon: {$iconName}"));
        }
        $svgAttributes = $this->generateSvgAttributes($class, $options);

        return str_replace('<svg', sprintf('<svg%s', $svgAttributes), $svgContent);
    }

    /**
     * @param array<string, string> $options
     */
    private function generateSvgAttributes(string $class, array $options): string
    {
        $attributes = sprintf(' class="%s"', $class);
        foreach ($options as $attr => $value) {
            $attributes .= sprintf(' %s="%s"', $attr, $value);
        }

        return $attributes;
    }
}
