# PHP Color

[![Tests](https://github.com/philiprehberger/php-color/actions/workflows/tests.yml/badge.svg)](https://github.com/philiprehberger/php-color/actions/workflows/tests.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/philiprehberger/php-color.svg)](https://packagist.org/packages/philiprehberger/php-color)
[![PHP Version Require](https://img.shields.io/packagist/php-v/philiprehberger/php-color.svg)](https://packagist.org/packages/philiprehberger/php-color)
[![License](https://img.shields.io/github/license/philiprehberger/php-color)](LICENSE)

Color parsing, conversion, manipulation, and WCAG contrast checking for PHP. Zero external dependencies.

---

## Requirements

| Dependency | Version |
|------------|---------|
| PHP        | ^8.2    |

---

## Installation

```bash
composer require philiprehberger/php-color
```

---

## Usage

### Creating Colors

```php
use PhilipRehberger\Color\Color;

$color = Color::hex('#ff6347');       // From hex
$color = Color::rgb(255, 99, 71);    // From RGB
$color = Color::hsl(9, 100, 64);     // From HSL
$color = Color::named('tomato');      // From CSS name
```

### Converting Colors

```php
$color = Color::hex('#ff6347');

$color->toHex();    // '#ff6347'
$color->toRgb();    // 'rgb(255, 99, 71)'
$color->toHsl();    // 'hsl(9, 100%, 63.9%)'
$color->toArray();  // ['r' => 255, 'g' => 99, 'b' => 71, 'alpha' => 1.0]
```

### Manipulating Colors

All manipulation methods return new `Color` instances (immutable).

```php
$color = Color::hex('#3366cc');

$color->lighten(20);        // Lighten by 20%
$color->darken(10);         // Darken by 10%
$color->saturate(15);       // Increase saturation by 15%
$color->desaturate(15);     // Decrease saturation by 15%
$color->invert();           // Invert the color
$color->grayscale();        // Convert to grayscale

$red = Color::hex('#ff0000');
$blue = Color::hex('#0000ff');
$red->mix($blue, 0.5);     // Mix two colors
```

### WCAG Contrast Checking

```php
$text = Color::hex('#333333');
$bg = Color::hex('#ffffff');

$text->contrastRatio($bg);    // 12.63
$text->meetsWcagAA($bg);      // true (>= 4.5:1)
$text->meetsWcagAAA($bg);     // true (>= 7:1)

// Also accepts hex strings directly
$text->contrastRatio('#ffffff');
```

### Generating Palettes

```php
use PhilipRehberger\Color\Palette;

$color = Color::hex('#ff6347');

Palette::complementary($color);     // [original, complement]
Palette::analogous($color);         // [left, original, right]
Palette::triadic($color);           // [original, +120deg, +240deg]
Palette::shades($color, 5);         // 5 progressively darker shades
Palette::tints($color, 5);          // 5 progressively lighter tints
```

---

## API

### Color

| Method | Description |
|--------|-------------|
| `Color::hex(string $hex): Color` | Create from hex string (3, 4, 6, or 8 digits) |
| `Color::rgb(int $r, int $g, int $b, float $alpha = 1.0): Color` | Create from RGB values |
| `Color::hsl(float $h, float $s, float $l, float $alpha = 1.0): Color` | Create from HSL values |
| `Color::named(string $name): Color` | Create from CSS named color |
| `->lighten(float $percent): Color` | Lighten by percentage |
| `->darken(float $percent): Color` | Darken by percentage |
| `->saturate(float $percent): Color` | Increase saturation |
| `->desaturate(float $percent): Color` | Decrease saturation |
| `->mix(Color $other, float $weight = 0.5): Color` | Mix with another color |
| `->invert(): Color` | Invert the color |
| `->grayscale(): Color` | Convert to grayscale |
| `->contrastRatio(Color\|string $other): float` | WCAG contrast ratio (1.0-21.0) |
| `->meetsWcagAA(Color\|string $background): bool` | Meets WCAG AA (4.5:1) |
| `->meetsWcagAAA(Color\|string $background): bool` | Meets WCAG AAA (7:1) |
| `->toHex(): string` | Output as hex string |
| `->toRgb(): string` | Output as rgb()/rgba() string |
| `->toHsl(): string` | Output as hsl()/hsla() string |
| `->toArray(): array` | Output as associative array |

### Palette

| Method | Description |
|--------|-------------|
| `Palette::complementary(Color $color): array` | Complementary pair |
| `Palette::analogous(Color $color, float $angle = 30.0): array` | Three analogous colors |
| `Palette::triadic(Color $color): array` | Three triadic colors |
| `Palette::shades(Color $color, int $count = 5): array` | Progressive darker shades |
| `Palette::tints(Color $color, int $count = 5): array` | Progressive lighter tints |

---

## Testing

```bash
composer install
vendor/bin/phpunit
```

Code style:

```bash
vendor/bin/pint
```

Static analysis:

```bash
vendor/bin/phpstan analyse
```

---

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for recent changes.

---

## License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.
