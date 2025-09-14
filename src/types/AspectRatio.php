<?php
namespace Doubleedesign\ACF\AdvancedImageField;

enum AspectRatio: string {
    case STANDARD = '4:3';
    case PORTRAIT = '3:4';
    case SQUARE = '1:1';
    case WIDE = '16:9';
    case TALL = '9:16';
    case CLASSIC = '3:2';
    case CLASSIC_PORTRAIT = '2:3';
    case CINEMATIC = '21:9';
    case CINEMASCOPE = '2.35:1';

    public static function fromString(string $value): ?self {
        return match ($value) {
            '4:3', '4/3', 'standard' => self::STANDARD,
            '3:4', '3/4', 'portrait' => self::PORTRAIT,
            '1:1', '1/1', 'square' => self::SQUARE,
            '16:9', '16/9', 'wide' => self::WIDE,
            '9:16', '9/16', 'tall' => self::TALL,
            '3:2', '3/2', 'classic' => self::CLASSIC,
            '2:3', '2/3', 'classic_portrait' => self::CLASSIC_PORTRAIT,
            '2.35:1', '2.35/1', '2.35', 'cinemascope' => self::CINEMASCOPE,
            default => null,
        };
    }
}
