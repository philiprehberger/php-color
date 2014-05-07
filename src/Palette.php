<?php

declare(strict_types=1);

namespace PhilipRehberger\Color;

/**
 * Static palette generators for creating color schemes.
 */
final class Palette
{
    /**
     * Generate the complementary color (opposite on the color wheel).
     *
     * @return array{Color, Color}
     */
    public static function complementary(Color $color): array
    {
        $hsl = Converter::rgbToHsl($color->getRed(), $color->getGreen(), $color->getBlue());
        $complement = Color::hsl(fmod($hsl['h'] + 180, 360), $hsl['s'], $hsl['l'], $color->getAlpha());

        return [$color, $complement];
    }

    /**
     * Generate analogous colors (adjacent on the color wheel).
     *
     * @return array{Color, Color, Color}
     */
    public static function analogous(Color $color, float $angle = 30.0): array
    {
        $hsl = Converter::rgbToHsl($color->getRed(), $color->getGreen(), $color->getBlue());
        $alpha = $color->getAlpha();

        return [
            Color::hsl(fmod($hsl['h'] - $angle + 360, 360), $hsl['s'], $hsl['l'], $alpha),
            $color,
            Color::hsl(fmod($hsl['h'] + $angle, 360), $hsl['s'], $hsl['l'], $alpha),
        ];
    }

    /**
     * Generate triadic colors (evenly spaced 120 degrees apart).
     *
     * @return array{Color, Color, Color}
     */
    public static function triadic(Color $color): array
    {
        $hsl = Converter::rgbToHsl($color->getRed(), $color->getGreen(), $color->getBlue());
        $alpha = $color->getAlpha();

        return [
            $color,
            Color::hsl(fmod($hsl['h'] + 120, 360), $hsl['s'], $hsl['l'], $alpha),
            Color::hsl(fmod($hsl['h'] + 240, 360), $hsl['s'], $hsl['l'], $alpha),
        ];
    }

    /**
     * Generate shades of a color (progressively darker).
     *
     * @param  int  $count  Number of shades to generate
     * @return Color[]
     */
    public static function shades(Color $color, int $count = 5): array
    {
        $shades = [];
        $step = 100 / ($count + 1);

        for ($i = 1; $i <= $count; $i++) {
            $shades[] = $color->darken($step * $i);
        }

        return $shades;
    }

    /**
     * Generate tints of a color (progressively lighter).
     *
     * @param  int  $count  Number of tints to generate
     * @return Color[]
     */
    public static function tints(Color $color, int $count = 5): array
    {
        $tints = [];
        $step = 100 / ($count + 1);

        for ($i = 1; $i <= $count; $i++) {
            $tints[] = $color->lighten($step * $i);
        }

        return $tints;
    }
}
