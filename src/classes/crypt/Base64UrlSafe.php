<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite\Crypt;

/**
 * Class Base64UrlSafe
 * [A-Z][a-z][0-9]\-_
 *
 * @package ParagonIE\ConstantTime
 */
abstract class Base64UrlSafe extends Base64
{
    /**
     * Uses bitwise operators instead of table-lookups to turn 6-bit integers
     * into 8-bit integers.
     *
     * Base64 character set:
     * [A-Z]      [a-z]      [0-9]      -     _
     * 0x41-0x5a, 0x61-0x7a, 0x30-0x39, 0x2d, 0x5f
     *
     * @param int $src
     *
     * @return int
     */
    protected static function decode6Bits(int $src) : int
    {
        $ret = -1;

        // if ($src > 0x40 && $src < 0x5b) $ret += $src - 0x41 + 1; // -64
        $ret += (((0x40 - $src) & ($src - 0x5b)) >> 8) & ($src - 64);

        // if ($src > 0x60 && $src < 0x7b) $ret += $src - 0x61 + 26 + 1; // -70
        $ret += (((0x60 - $src) & ($src - 0x7b)) >> 8) & ($src - 70);

        // if ($src > 0x2f && $src < 0x3a) $ret += $src - 0x30 + 52 + 1; // 5
        $ret += (((0x2f - $src) & ($src - 0x3a)) >> 8) & ($src + 5);

        // if ($src == 0x2c) $ret += 62 + 1;
        $ret += (((0x2c - $src) & ($src - 0x2e)) >> 8) & 63;

        // if ($src == 0x5f) ret += 63 + 1;
        $ret += (((0x5e - $src) & ($src - 0x60)) >> 8) & 64;

        return $ret;
    }

    /**
     * Uses bitwise operators instead of table-lookups to turn 8-bit integers
     * into 6-bit integers.
     *
     * @param int $src
     *
     * @return string
     */
    protected static function encode6Bits(int $src) : string
    {
        $diff = 0x41;

        // if ($src > 25) $diff += 0x61 - 0x41 - 26; // 6
        $diff += ((25 - $src) >> 8) & 6;

        // if ($src > 51) $diff += 0x30 - 0x61 - 26; // -75
        $diff -= ((51 - $src) >> 8) & 75;

        // if ($src > 61) $diff += 0x2d - 0x30 - 10; // -13
        $diff -= ((61 - $src) >> 8) & 13;

        // if ($src > 62) $diff += 0x5f - 0x2b - 1; // 3
        $diff += ((62 - $src) >> 8) & 49;

        return pack('C', $src + $diff);
    }
}
