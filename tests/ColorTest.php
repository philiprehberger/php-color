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

    // -------------------------------------------------------------------------
    // Blend mode tests
    // -------------------------------------------------------------------------

    public function test_blend_multiply_white_and_black(): void
    {
        $white = Color::hex('#ffffff');
        $black = Color::hex('#000000');

        $result = $white->blend($black, 'multiply');

        $this->assertSame('#000000', $result->toHex());
    }

    public function test_blend_multiply_white_and_white(): void
    {
        $white = Color::hex('#ffffff');

        $result = $white->blend($white, 'multiply');

        $this->assertSame('#ffffff', $result->toHex());
    }

    public function test_blend_multiply_color_with_white(): void
    {
        $red = Color::hex('#ff0000');
        $white = Color::hex('#ffffff');

        $result = $red->blend($white, 'multiply');

        $this->assertSame('#ff0000', $result->toHex());
    }

    public function test_blend_screen_black_and_black(): void
    {
        $black = Color::hex('#000000');

        $result = $black->blend($black, 'screen');

        $this->assertSame('#000000', $result->toHex());
    }

    public function test_blend_screen_black_and_white(): void
    {
        $black = Color::hex('#000000');
        $white = Color::hex('#ffffff');

        $result = $black->blend($white, 'screen');

        $this->assertSame('#ffffff', $result->toHex());
    }

    public function test_blend_overlay_gray_with_gray(): void
    {
        $gray = Color::rgb(128, 128, 128);

        $result = $gray->blend($gray, 'overlay');

        // Overlay of 50% gray with itself: since 128/255 ~= 0.502 >= 0.5,
        // formula: 1 - 2*(1-a)*(1-b), result should be roughly 128
        $arr = $result->toArray();
        $this->assertEqualsWithDelta(128, $arr['r'], 2);
        $this->assertEqualsWithDelta(128, $arr['g'], 2);
        $this->assertEqualsWithDelta(128, $arr['b'], 2);
    }

    public function test_blend_darken(): void
    {
        $red = Color::hex('#ff3366');
        $blue = Color::hex('#3366ff');

        $result = $red->blend($blue, 'darken');

        $this->assertSame(min(255, 51), $result->getRed());
        $this->assertSame(min(51, 102), $result->getGreen());
        $this->assertSame(min(102, 255), $result->getBlue());
    }

    public function test_blend_lighten(): void
    {
        $red = Color::hex('#ff3366');
        $blue = Color::hex('#3366ff');

        $result = $red->blend($blue, 'lighten');

        $this->assertSame(max(255, 51), $result->getRed());
        $this->assertSame(max(51, 102), $result->getGreen());
        $this->assertSame(max(102, 255), $result->getBlue());
    }

    public function test_blend_unknown_mode_throws_exception(): void
    {
        $color = Color::hex('#ff0000');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown blend mode: unknown');

        $color->blend($color, 'unknown');
    }

    // -------------------------------------------------------------------------
    // Gradient tests
    // -------------------------------------------------------------------------

    public function test_gradient_rgb_black_to_white(): void
    {
        $black = Color::hex('#000000');
        $white = Color::hex('#ffffff');

        $gradient = Palette::gradient($black, $white, 5);

        $this->assertCount(5, $gradient);
        $this->assertSame('#000000', $gradient[0]->toHex());
        $this->assertSame('#404040', $gradient[1]->toHex());
        $this->assertSame('#808080', $gradient[2]->toHex());
        $this->assertSame('#bfbfbf', $gradient[3]->toHex());
        $this->assertSame('#ffffff', $gradient[4]->toHex());
    }

    public function test_gradient_rgb_evenly_spaced(): void
    {
        $black = Color::hex('#000000');
        $white = Color::hex('#ffffff');

        $gradient = Palette::gradient($black, $white, 5);

        // Check that spacing between steps is roughly equal
        for ($i = 0; $i < 4; $i++) {
            $diff = $gradient[$i + 1]->getRed() - $gradient[$i]->getRed();
            $this->assertEqualsWithDelta(64, $diff, 1);
        }
    }

    public function test_gradient_hsl(): void
    {
        $red = Color::hex('#ff0000');
        $green = Color::hsl(120, 100, 50);

        $gradient = Palette::gradient($red, $green, 3, 'hsl');

        $this->assertCount(3, $gradient);
        $this->assertSame('#ff0000', $gradient[0]->toHex());
        // Middle step should be at hue 60 (yellow)
        $this->assertSame('#ffff00', $gradient[1]->toHex());
    }

    public function test_gradient_minimum_steps(): void
    {
        $black = Color::hex('#000000');
        $white = Color::hex('#ffffff');

        $gradient = Palette::gradient($black, $white, 2);

        $this->assertCount(2, $gradient);
        $this->assertSame('#000000', $gradient[0]->toHex());
        $this->assertSame('#ffffff', $gradient[1]->toHex());
    }

    public function test_gradient_throws_for_less_than_two_steps(): void
    {
        $color = Color::hex('#ff0000');

        $this->expectException(InvalidArgumentException::class);

        Palette::gradient($color, $color, 1);
    }

    public function test_gradient_throws_for_unknown_space(): void
    {
        $color = Color::hex('#ff0000');

        $this->expectException(InvalidArgumentException::class);

        Palette::gradient($color, $color, 3, 'lab');
    }

    // -------------------------------------------------------------------------
    // Split-complementary tests
    // -------------------------------------------------------------------------

    public function test_split_complementary_returns_three_colors(): void
    {
        $color = Color::hex('#ff0000');
        $palette = Palette::splitComplementary($color);

        $this->assertCount(3, $palette);
        $this->assertSame('#ff0000', $palette[0]->toHex());
    }

    public function test_split_complementary_hue_offsets(): void
    {
        // Use a color with known hue: red = 0 degrees
        $color = Color::hsl(0, 100, 50);
        $palette = Palette::splitComplementary($color);

        // base+150 should be hue 150, base+210 should be hue 210
        $hsl1 = $palette[1]->toArray();
        $hsl2 = $palette[2]->toArray();

        // Verify by creating expected colors directly
        $expected1 = Color::hsl(150, 100, 50);
        $expected2 = Color::hsl(210, 100, 50);

        $this->assertSame($expected1->toHex(), $palette[1]->toHex());
        $this->assertSame($expected2->toHex(), $palette[2]->toHex());
    }

    // -------------------------------------------------------------------------
    // Tetradic tests
    // -------------------------------------------------------------------------

    public function test_tetradic_returns_four_colors(): void
    {
        $color = Color::hex('#ff0000');
        $palette = Palette::tetradic($color);

        $this->assertCount(4, $palette);
        $this->assertSame('#ff0000', $palette[0]->toHex());
    }

    public function test_tetradic_hue_offsets(): void
    {
        $color = Color::hsl(0, 100, 50);
        $palette = Palette::tetradic($color);

        $expected90 = Color::hsl(90, 100, 50);
        $expected180 = Color::hsl(180, 100, 50);
        $expected270 = Color::hsl(270, 100, 50);

        $this->assertSame($expected90->toHex(), $palette[1]->toHex());
        $this->assertSame($expected180->toHex(), $palette[2]->toHex());
        $this->assertSame($expected270->toHex(), $palette[3]->toHex());
    }
}
