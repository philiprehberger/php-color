# Changelog

All notable changes to `php-color` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.2.1] - 2026-03-31

### Changed
- Standardize README to 3-badge format with emoji Support section
- Update CI checkout action to v5 for Node.js 24 compatibility

## [1.2.0] - 2026-03-27

### Added
- `Color::blend()` with multiply, screen, overlay, darken, and lighten blend modes
- `Palette::gradient()` for generating color gradients with RGB and HSL interpolation
- `Palette::splitComplementary()` and `Palette::tetradic()` palette generators

## [1.1.0] - 2026-03-22

### Added
- `distance()` method for perceptual color distance using CIE76 Delta E
- `isLight()` and `isDark()` convenience methods based on relative luminance
- `random()` static factory method for generating random colors

## [1.0.3] - 2026-03-17

### Changed
- Standardized package metadata, README structure, and CI workflow per package guide

## [1.0.2] - 2026-03-16

### Changed
- Standardize composer.json: add type, homepage, scripts

## [1.0.1] - 2026-03-15

### Changed
- Standardize README badges

## [1.0.0] - 2026-03-15

### Added
- Initial release
- Immutable `Color` value object with hex, RGB, HSL, and named color parsing
- Color manipulation: lighten, darken, saturate, desaturate, mix, invert, grayscale
- WCAG 2.0 contrast ratio calculation with AA and AAA compliance checks
- `Palette` generator for complementary, analogous, triadic, shades, and tints
- Full 148 CSS named colors support
- Zero external dependencies
