<?php

declare(strict_types=1);

namespace PhilipRehberger\Color\Tests;

use InvalidArgumentException;
use PhilipRehberger\Color\Color;
use PhilipRehberger\Color\Palette;
use PHPUnit\Framework\TestCase;

class ColorTest extends TestCase
{
    public function test_hex_parsing_six_digit(): void
    {
        $color = Color::hex('#ff0000');

        $this->assertSame('#ff0000', $color->toHex());
        $this->assertSame(255, $color->getRed());
        $this->assertSame(0, $color->getGreen());
        $this->assertSame(0, $color->getBlue());
    }

    public function test_hex_parsing_three_digit(): void
    {
        $color = Color::hex('#f00');

        $this->assertSame('#ff0000', $color->toHex());
    }

    public function test_hex_parsing_without_hash(): void
    {
        $color = Color::hex('00ff00');

        $this->assertSame('#00ff00', $color->toHex());
    }

    public function test_invalid_hex_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Color::hex('#xyz');
    }

    public function test_rgb_factory(): void
    {
        $color = Color::rgb(128, 64, 32);

        $this->assertSame(128, $color->getRed());
        $this->assertSame(64, $color->getGreen());
        $this->assertSame(32, $color->getBlue());
        $this->assertSame(1.0, $color->getAlpha());
    }

    public function test_rgb_clamps_values(): void
    {
        $color = Color::rgb(300, -10, 128);

        $this->assertSame(255, $color->getRed());
        $this->assertSame(0, $color->getGreen());
        $this->assertSame(128, $color->getBlue());
    }

    public function test_hsl_factory(): void
    {
        $color = Color::hsl(0, 100, 50);

        $this->assertSame('#ff0000', $color->toHex());
    }

    public function test_named_color(): void
    {
        $color = Color::named('coral');

        $this->assertSame('#ff7f50', $color->toHex());
    }

    public function test_unknown_named_color_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Color::named('notacolor');
    }

    public function test_lighten(): void
    {
        $color = Color::hex('#336699');
        $lighter = $color->lighten(20);

        $this->assertNotSame($color->toHex(), $lighter->toHex());
        // Lightened color should have higher lightness
        $originalArray = $color->toArray();
        $lighterArray = $lighter->toArray();
        $this->assertGreaterThan(
            $originalArray['r'] + $originalArray['g'] + $originalArray['b'],
            $lighterArray['r'] + $lighterArray['g'] + $lighterArray['b'],
        );
    }

    public function test_darken(): void
    {
        $color = Color::hex('#336699');
        $darker = $color->darken(20);

        $originalArray = $color->toArray();
        $darkerArray = $darker->toArray();
        $this->assertLessThan(
            $originalArray['r'] + $originalArray['g'] + $originalArray['b'],
            $darkerArray['r'] + $darkerArray['g'] + $darkerArray['b'],
        );
    }

    public function test_invert(): void
    {
        $color = Color::hex('#ff0000');
        $inverted = $color->invert();

        $this->assertSame('#00ffff', $inverted->toHex());
    }

    public function test_grayscale(): void
    {
        $color = Color::hex('#ff0000');
        $gray = $color->grayscale();

        $arr = $gray->toArray();
        $this->assertSame($arr['r'], $arr['g']);
        $this->assertSame($arr['g'], $arr['b']);
    }

    public function test_mix(): void
    {
        $red = Color::hex('#ff0000');
        $blue = Color::hex('#0000ff');
        $mixed = $red->mix($blue, 0.5);

        $arr = $mixed->toArray();
        $this->assertSame(128, $arr['r']);
        $this->assertSame(0, $arr['g']);
        $this->assertSame(128, $arr['b']);
    }

    public function test_contrast_ratio_black_white(): void
    {
        $black = Color::hex('#000000');
        $white = Color::hex('#ffffff');

        $this->assertSame(21.0, $black->contrastRatio($white));
    }

    public function test_meets_wcag_aa(): void
    {
        $darkText = Color::hex('#333333');
        $white = Color::hex('#ffffff');

        $this->assertTrue($darkText->meetsWcagAA($white));
    }

    public function test_meets_wcag_aaa(): void
    {
        $black = Color::hex('#000000');
        $white = Color::hex('#ffffff');

        $this->assertTrue($black->meetsWcagAAA($white));
    }

    public function test_fails_wcag_aa(): void
    {
        $lightGray = Color::hex('#cccccc');
        $white = Color::hex('#ffffff');

        $this->assertFalse($lightGray->meetsWcagAA($white));
    }

    public function test_to_rgb_string(): void
    {
        $color = Color::rgb(255, 128, 0);

        $this->assertSame('rgb(255, 128, 0)', $color->toRgb());
    }

    public function test_to_rgba_string(): void
    {
        $color = Color::rgb(255, 128, 0, 0.5);

        $this->assertSame('rgba(255, 128, 0, 0.5)', $color->toRgb());
    }

    public function test_to_hsl_string(): void
    {
        $color = Color::hex('#ff0000');
        $hsl = $color->toHsl();

        $this->assertStringStartsWith('hsl(', $hsl);
        $this->assertStringContainsString('100%', $hsl);
    }

    public function test_to_array(): void
    {
        $color = Color::rgb(10, 20, 30, 0.8);
        $arr = $color->toArray();

        $this->assertSame(['r' => 10, 'g' => 20, 'b' => 30, 'alpha' => 0.8], $arr);
    }

    public function test_immutability(): void
    {
        $original = Color::hex('#ff0000');
        $modified = $original->lighten(20);

        $this->assertSame('#ff0000', $original->toHex());
        $this->assertNotSame($original->toHex(), $modified->toHex());
    }

    public function test_palette_complementary(): void
    {
        $color = Color::hex('#ff0000');
        $palette = Palette::complementary($color);

        $this->assertCount(2, $palette);
        $this->assertSame('#ff0000', $palette[0]->toHex());
        // Complement of red is cyan
        $this->assertSame('#00ffff', $palette[1]->toHex());
    }

    public function test_palette_triadic(): void
    {
        $color = Color::hex('#ff0000');
        $palette = Palette::triadic($color);

        $this->assertCount(3, $palette);
        $this->assertSame('#ff0000', $palette[0]->toHex());
    }

    public function test_palette_shades(): void
    {
        $color = Color::hex('#3366cc');
        $shades = Palette::shades($color, 3);

        $this->assertCount(3, $shades);

        // Each shade should be darker than the previous
        $prevSum = $color->getRed() + $color->getGreen() + $color->getBlue();
        foreach ($shades as $shade) {
            $sum = $shade->getRed() + $shade->getGreen() + $shade->getBlue();
            $this->assertLessThanOrEqual($prevSum, $sum);
            $prevSum = $sum;
        }
    }

    public function test_palette_tints(): void
    {
        $color = Color::hex('#3366cc');
        $tints = Palette::tints($color, 3);

        $this->assertCount(3, $tints);

        // Each tint should be lighter than the previous
        $prevSum = $color->getRed() + $color->getGreen() + $color->getBlue();
        foreach ($tints as $tint) {
            $sum = $tint->getRed() + $tint->getGreen() + $tint->getBlue();
            $this->assertGreaterThanOrEqual($prevSum, $sum);
            $prevSum = $sum;
        }
    }

    public function test_contrast_ratio_with_string(): void
    {
        $color = Color::hex('#000000');

        $this->assertSame(21.0, $color->contrastRatio('#ffffff'));
    }

    public function test_distance_identical_colors(): void
    {
        $color = Color::hex('#ff0000');

        $this->assertSame(0.0, $color->distance($color));
    }

    public function test_distance_black_and_white(): void
    {
        $black = Color::hex('#000000');
        $white = Color::hex('#ffffff');

        $distance = $black->distance($white);

        // CIE76 Delta E between black and white is approximately 100
        $this->assertGreaterThan(95, $distance);
        $this->assertLessThan(105, $distance);
    }

    public function test_distance_is_symmetric(): void
    {
        $red = Color::hex('#ff0000');
        $blue = Color::hex('#0000ff');

        $this->assertSame($red->distance($blue), $blue->distance($red));
    }

    public function test_is_light_with_white(): void
    {
        $white = Color::hex('#ffffff');

        $this->assertTrue($white->isLight());
    }

    public function test_is_light_with_black(): void
    {
        $black = Color::hex('#000000');

        $this->assertFalse($black->isLight());
    }

    public function test_is_light_with_medium_gray(): void
    {
        // #808080 has luminance ~0.216, so it should be dark
        $gray = Color::hex('#808080');

        $this->assertFalse($gray->isLight());

        // #bbbbbb has luminance ~0.486, still dark
        // #c0c0c0 has luminance ~0.527, should be light
        $lightGray = Color::hex('#c0c0c0');

        $this->assertTrue($lightGray->isLight());
    }

    public function test_is_dark_with_black(): void
    {
        $black = Color::hex('#000000');

        $this->assertTrue($black->isDark());
    }

    public function test_is_dark_with_white(): void
    {
        $white = Color::hex('#ffffff');

        $this->assertFalse($white->isDark());
    }

    public function test_is_dark_is_inverse_of_is_light(): void
    {
        $colors = [
            Color::hex('#ff0000'),
            Color::hex('#00ff00'),
            Color::hex('#0000ff'),
            Color::hex('#808080'),
            Color::hex('#ffffff'),
            Color::hex('#000000'),
        ];

        foreach ($colors as $color) {
            $this->assertNotSame($color->isLight(), $color->isDark());
        }
    }

    public function test_random_returns_valid_color(): void
    {
        $color = Color::random();

        $this->assertInstanceOf(Color::class, $color);
        $this->assertGreaterThanOrEqual(0, $color->getRed());
        $this->assertLessThanOrEqual(255, $color->getRed());
        $this->assertGreaterThanOrEqual(0, $color->getGreen());
        $this->assertLessThanOrEqual(255, $color->getGreen());
        $this->assertGreaterThanOrEqual(0, $color->getBlue());
        $this->assertLessThanOrEqual(255, $color->getBlue());
        $this->assertSame(1.0, $color->getAlpha());
    }

    public function test_random_returns_different_colors(): void
    {
        $colors = [];
        for ($i = 0; $i < 10; $i++) {
            $colors[] = Color::random()->toHex();
        }

        // With 10 random colors, at least 2 should be different
        $this->assertGreaterThan(1, count(array_unique($colors)));
    }
}
