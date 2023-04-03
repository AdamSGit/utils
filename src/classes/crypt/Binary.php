<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite\Crypt;

use Velocite\VelociteException;

/**
 * Class Binary
 *
 * Binary string operators that don't choke on
 * mbstring.func_overload
 */
abstract class Binary
{
    /**
     * Safe string length
     *
     * @ref mbstring.func_overload
     *
     * @param string $str
     *
     * @return int
     */
    public static function safeStrlen(string $str) : int
    {
        if (function_exists('mb_strlen'))
        {
            return (int) mb_strlen($str, '8bit');
        }


        return (int) strlen($str);
    }

    /**
     * Safe substring
     *
     * @ref mbstring.func_overload
     *
     * @staticvar boolean $exists
     *
     * @param string $str
     * @param int    $start
     * @param int    $length
     *
     * @return string
     */
    public static function safeSubstr(string $str, int $start = 0, ?int $length = null) : string
    {
        if ($length === 0)
        {
            return '';
        }

        if (function_exists('mb_substr'))
        {
            return mb_substr($str, $start, $length, '8bit');
        }
        // Unlike mb_substr(), substr() doesn't accept NULL for length
        if ($length !== null)
        {
            return substr($str, $start, $length);
        }


        return substr($str, $start);
    }

    /**
     * Evaluate whether or not two strings are equal (in constant-time)
     *
     * @param string $left
     * @param string $right
     *
     * @throws VelociteException
     *
     * @return bool
     */
    public static function hashEquals(string $left, string $right) : bool
    {
        if ( ! is_string($left))
        {
            throw new VelociteException('Argument 1 must be a string, ' . gettype($left) . ' given.');
        }

        if ( ! is_string($right))
        {
            throw new VelociteException('Argument 2 must be a string, ' . gettype($right) . ' given.');
        }

        if (is_callable('hash_equals'))
        {
            return hash_equals($left, $right);
        }
        $d = 0;

        $len = Str::strlen($left, '8bit');

        if ($len !== Str::strlen($right, '8bit'))
        {
            return false;
        }

        for ($i = 0; $i < $len; ++$i)
        {
            $d |= self::chrToInt($left[$i]) ^ self::chrToInt($right[$i]);
        }

        if ($d !== 0)
        {
            return false;
        }

        return $left === $right;
    }

    /**
     * Cache-timing-safe variant of ord()
     *
     * @internal You should not use this directly from another application
     *
     * @param string $chr
     *
     * @throws FuelException
     *
     * @return int
     */
    public static function chrToInt(string $chr) : int
    {
        if ( ! is_string($chr))
        {
            throw new VelociteException('Argument 1 must be a string, ' . gettype($chr) . ' given.');
        }

        if ( Str::strlen($chr, '8bit') !== 1)
        {
            throw new VelociteException('chrToInt() expects a string that is exactly 1 character long');
        }

        $chunk = unpack('C', $chr);

        return (int) ($chunk[1]);
    }
}
