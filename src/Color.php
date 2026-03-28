<?php

declare(strict_types=1);

namespace PhilipRehberger\Color;

/**
 * Immutable color value object with parsing, conversion, manipulation, and WCAG contrast checking.
 */
final readonly class Color
{
    private function __construct(
        private int $red,
        private int $green,
        private int $blue,
        private float $alpha,
    ) {}

    // -------------------------------------------------------------------------
    // Factory methods
    // -------------------------------------------------------------------------

    /**
     * Create a color from a hex string.
     *
     * Accepts 3, 4, 6, or 8 character hex values with or without leading #.
     */
    public static function hex(string $hex): self
    {
        $hex = ltrim(trim($hex), '#');

        if (! preg_match('/^([0-9a-fA-F]{3}|[0-9a-fA-F]{4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $hex)) {
            throw new \InvalidArgumentException("Invalid hex color: #{$hex}");
        }

        $rgb = Converter::hexToRgb($hex);

        return new self($rgb['r'], $rgb['g'], $rgb['b'], $rgb['alpha']);
    }

    /**
     * Create a color from RGB values.
     */
    public static function rgb(int $r, int $g, int $b, float $alpha = 1.0): self
    {
        return new self(
            Converter::clampInt($r),
            Converter::clampInt($g),
            Converter::clampInt($b),
            Converter::clampFloat($alpha),
        );
    }

    /**
     * Create a color from HSL values.
     *
     * @param  float  $h  Hue in degrees (0-360)
     * @param  float  $s  Saturation percentage (0-100)
     * @param  float  $l  Lightness percentage (0-100)
     */
    public static function hsl(float $h, float $s, float $l, float $alpha = 1.0): self
    {
        $rgb = Converter::hslToRgb($h, $s, $l);

        return new self($rgb['r'], $rgb['g'], $rgb['b'], Converter::clampFloat($alpha));
    }

    /**
     * Create a color from a CSS named color.
     */
    public static function named(string $name): self
    {
        $hex = NamedColors::resolve($name);

        if ($hex === null) {
            throw new \InvalidArgumentException("Unknown color name: {$name}");
        }

        return self::hex($hex);
    }

    /**
     * Generate a random color.
     */
    public static function random(): static
    {
        return new self(random_int(0, 255), random_int(0, 255), random_int(0, 255), 1.0);
    }

    // -------------------------------------------------------------------------
    // Manipulation methods
    // -------------------------------------------------------------------------

    /**
     * Lighten the color by a percentage.
     *
     * @param  float  $percent  Amount to lighten (0-100)
     */
    public function lighten(float $percent): self
    {
        $hsl = Converter::rgbToHsl($this->red, $this->green, $this->blue);
        $hsl['l'] = min(100, $hsl['l'] + $percent);
        $rgb = Converter::hslToRgb($hsl['h'], $hsl['s'], $hsl['l']);

        return new self($rgb['r'], $rgb['g'], $rgb['b'], $this->alpha);
    }

    /**
     * Darken the color by a percentage.
     *
     * @param  float  $percent  Amount to darken (0-100)
     */
    public function darken(float $percent): self
    {
        $hsl = Converter::rgbToHsl($this->red, $this->green, $this->blue);
        $hsl['l'] = max(0, $hsl['l'] - $percent);
        $rgb = Converter::hslToRgb($hsl['h'], $hsl['s'], $hsl['l']);

        return new self($rgb['r'], $rgb['g'], $rgb['b'], $this->alpha);
    }

    /**
     * Increase the saturation by a percentage.
     *
     * @param  float  $percent  Amount to saturate (0-100)
     */
    public function saturate(float $percent): self
    {
        $hsl = Converter::rgbToHsl($this->red, $this->green, $this->blue);
        $hsl['s'] = min(100, $hsl['s'] + $percent);
        $rgb = Converter::hslToRgb($hsl['h'], $hsl['s'], $hsl['l']);

        return new self($rgb['r'], $rgb['g'], $rgb['b'], $this->alpha);
    }

    /**
     * Decrease the saturation by a percentage.
     *
     * @param  float  $percent  Amount to desaturate (0-100)
     */
    public function desaturate(float $percent): self
    {
        $hsl = Converter::rgbToHsl($this->red, $this->green, $this->blue);
        $hsl['s'] = max(0, $hsl['s'] - $percent);
        $rgb = Converter::hslToRgb($hsl['h'], $hsl['s'], $hsl['l']);

        return new self($rgb['r'], $rgb['g'], $rgb['b'], $this->alpha);
    }

    /**
     * Mix this color with another color.
     *
     * @param  float  $weight  Weight of the other color (0.0 to 1.0)
     */
    public function mix(self $other, float $weight = 0.5): self
    {
        $weight = Converter::clampFloat($weight);

        $r = (int) round($this->red * (1 - $weight) + $other->red * $weight);
        $g = (int) round($this->green * (1 - $weight) + $other->green * $weight);
        $b = (int) round($this->blue * (1 - $weight) + $other->blue * $weight);
        $a = $this->alpha * (1 - $weight) + $other->alpha * $weight;

        return new self(
            Converter::clampInt($r),
            Converter::clampInt($g),
            Converter::clampInt($b),
            Converter::clampFloat($a),
        );
    }

    /**
     * Blend this color with another using a CSS blend mode.
     *
     * Supported modes: multiply, screen, overlay, darken, lighten.
     *
     * @throws \InvalidArgumentException If the blend mode is unknown
     */
    public function blend(self $other, string $mode): self
    {
        $a = [$this->red / 255, $this->green / 255, $this->blue / 255];
        $b = [$other->red / 255, $other->green / 255, $other->blue / 255];

        $result = match ($mode) {
            'multiply' => [
                $a[0] * $b[0],
                $a[1] * $b[1],
                $a[2] * $b[2],
            ],
            'screen' => [
                1 - (1 - $a[0]) * (1 - $b[0]),
                1 - (1 - $a[1]) * (1 - $b[1]),
                1 - (1 - $a[2]) * (1 - $b[2]),
            ],
            'overlay' => [
                $a[0] < 0.5 ? 2 * $a[0] * $b[0] : 1 - 2 * (1 - $a[0]) * (1 - $b[0]),
                $a[1] < 0.5 ? 2 * $a[1] * $b[1] : 1 - 2 * (1 - $a[1]) * (1 - $b[1]),
                $a[2] < 0.5 ? 2 * $a[2] * $b[2] : 1 - 2 * (1 - $a[2]) * (1 - $b[2]),
            ],
            'darken' => [
                min($a[0], $b[0]),
                min($a[1], $b[1]),
                min($a[2], $b[2]),
            ],
            'lighten' => [
                max($a[0], $b[0]),
                max($a[1], $b[1]),
                max($a[2], $b[2]),
            ],
            default => throw new \InvalidArgumentException("Unknown blend mode: {$mode}"),
        };

        return new self(
            Converter::clampInt((int) round($result[0] * 255)),
            Converter::clampInt((int) round($result[1] * 255)),
            Converter::clampInt((int) round($result[2] * 255)),
            $this->alpha,
        );
    }

    /**
     * Invert the color.
     */
    public function invert(): self
    {
        return new self(255 - $this->red, 255 - $this->green, 255 - $this->blue, $this->alpha);
    }

    /**
     * Convert the color to grayscale.
     */
    public function grayscale(): self
    {
        $hsl = Converter::rgbToHsl($this->red, $this->green, $this->blue);
        $rgb = Converter::hslToRgb($hsl['h'], 0, $hsl['l']);

        return new self($rgb['r'], $rgb['g'], $rgb['b'], $this->alpha);
    }

    // -------------------------------------------------------------------------
    // Analysis methods
    // -------------------------------------------------------------------------

    /**
     * Calculate the perceptual distance to another color using the CIE76 Delta E formula.
     */
    public function distance(self $other): float
    {
        $lab1 = $this->toLab();
        $lab2 = $other->toLab();

        return sqrt(
            ($lab1['L'] - $lab2['L']) ** 2
            + ($lab1['a'] - $lab2['a']) ** 2
            + ($lab1['b'] - $lab2['b']) ** 2,
        );
    }

    /**
     * Determine if this color is light (relative luminance > 0.5).
     */
    public function isLight(): bool
    {
        return $this->relativeLuminance() > 0.5;
    }

    /**
     * Determine if this color is dark (relative luminance <= 0.5).
     */
    public function isDark(): bool
    {
        return $this->relativeLuminance() <= 0.5;
    }

    // -------------------------------------------------------------------------
    // WCAG contrast methods
    // -------------------------------------------------------------------------

    /**
     * Calculate the WCAG contrast ratio between this color and another.
     *
     * @return float Contrast ratio between 1.0 and 21.0
     */
    public function contrastRatio(self|string $other): float
    {
        $other = $other instanceof self ? $other : self::hex($other);

        $l1 = $this->relativeLuminance();
        $l2 = $other->relativeLuminance();

        $lighter = max($l1, $l2);
        $darker = min($l1, $l2);

        return round(($lighter + 0.05) / ($darker + 0.05), 2);
    }

    /**
     * Check if this color meets WCAG AA contrast requirements against a background.
     *
     * Requires a contrast ratio of at least 4.5:1 for normal text.
     */
    public function meetsWcagAA(self|string $background): bool
    {
        return $this->contrastRatio($background) >= 4.5;
    }

    /**
     * Check if this color meets WCAG AAA contrast requirements against a background.
     *
     * Requires a contrast ratio of at least 7:1 for normal text.
     */
    public function meetsWcagAAA(self|string $background): bool
    {
        return $this->contrastRatio($background) >= 7.0;
    }

    // -------------------------------------------------------------------------
    // Output methods
    // -------------------------------------------------------------------------

    /**
     * Get the color as a hex string.
     */
    public function toHex(): string
    {
        return Converter::rgbToHex($this->red, $this->green, $this->blue);
    }

    /**
     * Get the color as an rgb() or rgba() string.
     */
    public function toRgb(): string
    {
        if ($this->alpha < 1.0) {
            return sprintf('rgba(%d, %d, %d, %s)', $this->red, $this->green, $this->blue, rtrim(rtrim(number_format($this->alpha, 2), '0'), '.'));
        }

        return sprintf('rgb(%d, %d, %d)', $this->red, $this->green, $this->blue);
    }

    /**
     * Get the color as an hsl() or hsla() string.
     */
    public function toHsl(): string
    {
        $hsl = Converter::rgbToHsl($this->red, $this->green, $this->blue);

        if ($this->alpha < 1.0) {
            return sprintf(
                'hsla(%s, %s%%, %s%%, %s)',
                rtrim(rtrim(number_format($hsl['h'], 1), '0'), '.'),
                rtrim(rtrim(number_format($hsl['s'], 1), '0'), '.'),
                rtrim(rtrim(number_format($hsl['l'], 1), '0'), '.'),
                rtrim(rtrim(number_format($this->alpha, 2), '0'), '.'),
            );
        }

        return sprintf(
            'hsl(%s, %s%%, %s%%)',
            rtrim(rtrim(number_format($hsl['h'], 1), '0'), '.'),
            rtrim(rtrim(number_format($hsl['s'], 1), '0'), '.'),
            rtrim(rtrim(number_format($hsl['l'], 1), '0'), '.'),
        );
    }

    /**
     * Get the color components as an array.
     *
     * @return array{r: int, g: int, b: int, alpha: float}
     */
    public function toArray(): array
    {
        return [
            'r' => $this->red,
            'g' => $this->green,
            'b' => $this->blue,
            'alpha' => $this->alpha,
        ];
    }

    /**
     * Get the red component.
     */
    public function getRed(): int
    {
        return $this->red;
    }

    /**
     * Get the green component.
     */
    public function getGreen(): int
    {
        return $this->green;
    }

    /**
     * Get the blue component.
     */
    public function getBlue(): int
    {
        return $this->blue;
    }

    /**
     * Get the alpha component.
     */
    public function getAlpha(): float
    {
        return $this->alpha;
    }

    /**
     * Calculate the relative luminance of this color (WCAG 2.0).
     */
    private function relativeLuminance(): float
    {
        $r = $this->linearize($this->red / 255);
        $g = $this->linearize($this->green / 255);
        $b = $this->linearize($this->blue / 255);

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    /**
     * Linearize an sRGB channel value for luminance calculation.
     */
    private function linearize(float $value): float
    {
        return $value <= 0.04045
            ? $value / 12.92
            : (($value + 0.055) / 1.055) ** 2.4;
    }

    /**
     * Convert this color to CIE XYZ color space.
     *
     * @return array{X: float, Y: float, Z: float}
     */
    private function toXyz(): array
    {
        $r = $this->linearize($this->red / 255);
        $g = $this->linearize($this->green / 255);
        $b = $this->linearize($this->blue / 255);

        return [
            'X' => $r * 0.4124564 + $g * 0.3575761 + $b * 0.1804375,
            'Y' => $r * 0.2126729 + $g * 0.7151522 + $b * 0.0721750,
            'Z' => $r * 0.0193339 + $g * 0.1191920 + $b * 0.9503041,
        ];
    }

    /**
     * Convert this color to CIE Lab color space using D65 illuminant.
     *
     * @return array{L: float, a: float, b: float}
     */
    private function toLab(): array
    {
        $xyz = $this->toXyz();

        // D65 reference white
        $xn = 0.95047;
        $yn = 1.00000;
        $zn = 1.08883;

        $fx = $this->labF($xyz['X'] / $xn);
        $fy = $this->labF($xyz['Y'] / $yn);
        $fz = $this->labF($xyz['Z'] / $zn);

        return [
            'L' => 116 * $fy - 16,
            'a' => 500 * ($fx - $fy),
            'b' => 200 * ($fy - $fz),
        ];
    }

    /**
     * CIE Lab transfer function.
     */
    private function labF(float $t): float
    {
        $delta = 6 / 29;

        return $t > $delta ** 3
            ? $t ** (1 / 3)
            : $t / (3 * $delta ** 2) + 4 / 29;
    }
}
