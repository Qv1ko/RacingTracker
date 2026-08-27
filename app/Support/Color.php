<?php

declare(strict_types=1);

namespace App\Support;

final class Color
{
    public static function fromString(string $seed): string
    {
        $hash = crc32(mb_strtolower(trim($seed)));
        $hue = $hash % 360;
        $saturation = 65;
        $lightness = 55;

        return self::hslToHex($hue, $saturation, $lightness);
    }

    private static function hslToHex(int $h, int $s, int $l): string
    {
        $s /= 100;
        $l /= 100;

        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l - $c / 2;

        $r = 0;
        $g = 0;
        $b = 0;

        if ($h < 60) {
            $r = $c;
            $g = $x;
        } elseif ($h < 120) {
            $r = $x;
            $g = $c;
        } elseif ($h < 180) {
            $g = $c;
            $b = $x;
        } elseif ($h < 240) {
            $g = $x;
            $b = $c;
        } elseif ($h < 300) {
            $r = $x;
            $b = $c;
        } else {
            $r = $c;
            $b = $x;
        }

        $toHex = fn (float $v): string => str_pad(
            dechex((int) round(($v + $m) * 255)),
            2,
            '0',
            STR_PAD_LEFT,
        );

        return '#'.$toHex($r).$toHex($g).$toHex($b);
    }
}
