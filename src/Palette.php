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
     * Generate a split-complementary palette.
     *
     * Returns 3 colors: base, base+150 degrees, base+210 degrees.
     *
     * @return array{Color, Color, Color}
     */
    public static function splitComplementary(Color $color): array
    {
        $hsl = Converter::rgbToHsl($color->getRed(), $color->getGreen(), $color->getBlue());
        $alpha = $color->getAlpha();

        return [
            $color,
            Color::hsl(fmod($hsl['h'] + 150, 360), $hsl['s'], $hsl['l'], $alpha),
            Color::hsl(fmod($hsl['h'] + 210, 360), $hsl['s'], $hsl['l'], $alpha),
        ];
    }

    /**
     * Generate a tetradic (rectangular) palette.
     *
     * Returns 4 colors: base, base+90 degrees, base+180 degrees, base+270 degrees.
     *
     * @return array{Color, Color, Color, Color}
     */
    public static function tetradic(Color $color): array
    {
        $hsl = Converter::rgbToHsl($color->getRed(), $color->getGreen(), $color->getBlue());
        $alpha = $color->getAlpha();

        return [
            $color,
            Color::hsl(fmod($hsl['h'] + 90, 360), $hsl['s'], $hsl['l'], $alpha),
            Color::hsl(fmod($hsl['h'] + 180, 360), $hsl['s'], $hsl['l'], $alpha),
            Color::hsl(fmod($hsl['h'] + 270, 360), $hsl['s'], $hsl['l'], $alpha),
        ];
    }

    /**
     * Generate a gradient between two colors.
     *
     * @param  int  $steps  Number of colors in the gradient (minimum 2)
     * @param  string  $space  Color space for interpolation: 'rgb' or 'hsl'
     * @return Color[]
     *
     * @throws \InvalidArgumentException If steps < 2 or space is invalid
     */
    public static function gradient(Color $start, Color $end, int $steps, string $space = 'rgb'): array
    {
        if ($steps < 2) {
            throw new \InvalidArgumentException('Gradient requires at least 2 steps.');
        }

        if ($space === 'rgb') {
            return self::gradientRgb($start, $end, $steps);
        }

        if ($space === 'hsl') {
            return self::gradientHsl($start, $end, $steps);
        }

        throw new \InvalidArgumentException("Unknown color space: {$space}");
    }

    /**
     * @return Color[]
     */
    private static function gradientRgb(Color $start, Color $end, int $steps): array
    {
        $colors = [];
        $divisions = $steps - 1;

        for ($i = 0; $i < $steps; $i++) {
            $t = $i / $divisions;
            $colors[] = Color::rgb(
                (int) round($start->getRed() + ($end->getRed() - $start->getRed()) * $t),
                (int) round($start->getGreen() + ($end->getGreen() - $start->getGreen()) * $t),
                (int) round($start->getBlue() + ($end->getBlue() - $start->getBlue()) * $t),
                $start->getAlpha() + ($end->getAlpha() - $start->getAlpha()) * $t,
            );
        }

        return $colors;
    }

    /**
     * @return Color[]
     */
    private static function gradientHsl(Color $start, Color $end, int $steps): array
    {
        $startHsl = Converter::rgbToHsl($start->getRed(), $start->getGreen(), $start->getBlue());
        $endHsl = Converter::rgbToHsl($end->getRed(), $end->getGreen(), $end->getBlue());

        // Take shortest hue path
        $hueDiff = $endHsl['h'] - $startHsl['h'];
        if ($hueDiff > 180) {
            $hueDiff -= 360;
        } elseif ($hueDiff < -180) {
            $hueDiff += 360;
        }

        $colors = [];
        $divisions = $steps - 1;

        for ($i = 0; $i < $steps; $i++) {
            $t = $i / $divisions;
            $h = fmod($startHsl['h'] + $hueDiff * $t + 360, 360);
            $s = $startHsl['s'] + ($endHsl['s'] - $startHsl['s']) * $t;
            $l = $startHsl['l'] + ($endHsl['l'] - $startHsl['l']) * $t;
            $a = $start->getAlpha() + ($end->getAlpha() - $start->getAlpha()) * $t;

            $colors[] = Color::hsl($h, $s, $l, $a);
        }

        return $colors;
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
