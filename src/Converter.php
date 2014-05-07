<?php

declare(strict_types=1);

namespace PhilipRehberger\Color;

/**
 * Internal color conversion math between RGB, HSL, and Hex formats.
 *
 * @internal
 */
final class Converter
{
    /**
     * Convert hex string to RGB array.
     *
     * @return array{r: int, g: int, b: int, alpha: float}
     */
    public static function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (strlen($hex) === 4) {
            $alpha = hexdec($hex[3].$hex[3]) / 255;
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];

            return [
                'r' => (int) hexdec(substr($hex, 0, 2)),
                'g' => (int) hexdec(substr($hex, 2, 2)),
                'b' => (int) hexdec(substr($hex, 4, 2)),
                'alpha' => round($alpha, 2),
            ];
        }

        if (strlen($hex) === 8) {
            $alpha = hexdec(substr($hex, 6, 2)) / 255;

            return [
                'r' => (int) hexdec(substr($hex, 0, 2)),
                'g' => (int) hexdec(substr($hex, 2, 2)),
                'b' => (int) hexdec(substr($hex, 4, 2)),
                'alpha' => round($alpha, 2),
            ];
        }

        return [
            'r' => (int) hexdec(substr($hex, 0, 2)),
            'g' => (int) hexdec(substr($hex, 2, 2)),
            'b' => (int) hexdec(substr($hex, 4, 2)),
            'alpha' => 1.0,
        ];
    }

    /**
     * Convert RGB values to hex string.
     */
    public static function rgbToHex(int $r, int $g, int $b): string
    {
        return sprintf('#%02x%02x%02x', self::clampInt($r), self::clampInt($g), self::clampInt($b));
    }

    /**
     * Convert RGB values to HSL array.
     *
     * @return array{h: float, s: float, l: float}
     */
    public static function rgbToHsl(int $r, int $g, int $b): array
    {
        $r /= 255;
        $g /= 255;
        $b /= 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;

        if ($max === $min) {
            return ['h' => 0.0, 's' => 0.0, 'l' => round($l * 100, 2)];
        }

        $d = $max - $min;
        $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);

        $h = match (true) {
            $max === $r => (($g - $b) / $d + ($g < $b ? 6 : 0)) / 6,
            $max === $g => (($b - $r) / $d + 2) / 6,
            default => (($r - $g) / $d + 4) / 6,
        };

        return [
            'h' => round($h * 360, 2),
            's' => round($s * 100, 2),
            'l' => round($l * 100, 2),
        ];
    }

    /**
     * Convert HSL values to RGB array.
     *
     * @return array{r: int, g: int, b: int}
     */
    public static function hslToRgb(float $h, float $s, float $l): array
    {
        $h = fmod($h, 360);
        if ($h < 0) {
            $h += 360;
        }
        $s /= 100;
        $l /= 100;

        if ($s === 0.0) {
            $v = (int) round($l * 255);

            return ['r' => $v, 'g' => $v, 'b' => $v];
        }

        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l - $c / 2;

        [$r, $g, $b] = match (true) {
            $h < 60 => [$c, $x, 0.0],
            $h < 120 => [$x, $c, 0.0],
            $h < 180 => [0.0, $c, $x],
            $h < 240 => [0.0, $x, $c],
            $h < 300 => [$x, 0.0, $c],
            default => [$c, 0.0, $x],
        };

        return [
            'r' => (int) round(($r + $m) * 255),
            'g' => (int) round(($g + $m) * 255),
            'b' => (int) round(($b + $m) * 255),
        ];
    }

    /**
     * Clamp an integer to the 0-255 range.
     */
    public static function clampInt(int $value, int $min = 0, int $max = 255): int
    {
        return max($min, min($max, $value));
    }

    /**
     * Clamp a float to a given range.
     */
    public static function clampFloat(float $value, float $min = 0.0, float $max = 1.0): float
    {
        return max($min, min($max, $value));
    }
}
