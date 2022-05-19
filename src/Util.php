<?php


namespace Lat\Ecc;

trait Util
{
    public function hexToBytes($hex)
    {
        if (strlen($hex) % 2 != 0) { // 奇数位补0
            $hex = "0" . $hex;
        }
        $bytes = [];
        $len   = strlen($hex);
        for ($i = 0; $i < $len; $i++) {
            $bytes[] = (int)base_convert($hex[$i] . $hex[++$i], 16, 10);
        }

        return $bytes;
    }

    public function bytesToHex($data)
    {
        $hex = '';
        foreach ($data as $value) {
//            $hex .= $this->getHex(base_convert($value, 10, 16));
            $hex .= $this->getHex(gmp_strval($value, 16));
        }

        return $hex;
    }

    public function leftRotate($x, $i)
    {
        $i %= 32;
        $aa = pow(2, $i);
        $a = gmp_init(strval($aa), 10);
        $b = gmp_init('0xFFFFFFFF', 16);
        $c = gmp_init(strval(pow(2, 32 - $i)), 10);
        return gmp_or(gmp_and(gmp_mul($x, $a), $b), gmp_div($x, $c));

        return (($x << $i) & 0xFFFFFFFF) | ($x >> (32 - $i));
    }

    public function strToBytes($string)
    {
        return unpack("C*", $string);
    }

    public function bytesToStr($bytes)
    {
        array_unshift($bytes, 'C' . count($bytes));

        return call_user_func_array('pack', $bytes);
    }

    public function bytesToInt($bytes)
    {
        $val = 0;
        $pos = 0;

        $val = $bytes[$pos + 0] & 0xff;

        $val <<= 8;

        $val |= $bytes[$pos + 1] & 0xff;

        $val <<= 8;

        $val |= $bytes[$pos + 2] & 0xff;
        $val = gmp_or(gmp_mul(gmp_init($val), gmp_init(pow(2, 8))), gmp_init($bytes[$pos + 3] & 0xff));

        return $val;
    }

    public function add(...$a)
    {
        $sum = 0;
        foreach ($a as $value) {
            $sum = gmp_add($sum, $value);
        }
        $base = gmp_init('0xFFFFFFFF', 16);
        if (gmp_cmp($sum, $base)) {
            $sum = gmp_and($sum, $base);
        }
        /*$sum = array_sum($a);
        $sum > 0xFFFFFFFF && $sum &= 0xFFFFFFFF;*/

        return $sum;
    }

    public function getHex($number, $count = 8)
    {
        return str_pad($number, $count, "0", STR_PAD_LEFT);
    }

    public function generate($numBits = 256)
    {
        $value   = gmp_random_bits($numBits);
        $mask    = gmp_sub(gmp_pow(2, $numBits), 1);
        $integer = gmp_and($value, $mask);

        return $integer;
    }

    public function decHex($dec, $len = 0)
    {
        if (!$dec instanceof \GMP) {
            $dec = gmp_init($dec, 10);
        }
        if (gmp_cmp($dec, 0) < 0) {
            throw new \Exception('Unable to convert negative integer to string');
        }

        $hex = gmp_strval($dec, 16);

        if (strlen($hex) % 2 != 0) {
            $hex = '0' . $hex;
        }
        if ($len && strlen($hex) < $len) {  // point x y 要补齐 64 位
            $hex = str_pad($hex, $len, "0", STR_PAD_LEFT);
        }

        return $hex;
    }

    public function strToInt($string)
    {
        $hex = unpack('H*', $string);

        return gmp_init($hex[1], 16);
    }
}