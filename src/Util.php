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
        $len = strlen($hex);
        for ($i = 0; $i < $len; $i++) {
            $bytes[] = (int)base_convert($hex[$i] . $hex[++$i], 16, 10);
        }

        return $bytes;
    }

    public function bytesToHex($data)
    {
        $hex = '';
        foreach ($data as $value) {
            $hex .= $this->decHex($value, 8);
        }

        return $hex;
    }

    public function leftRotate($x, $i)
    {
        $i %= 32;

        return (($x << $i) & 0xFFFFFFFF) | ($x >> (32 - $i));
    }

    public function strToBytes($string)
    {
        return unpack("C*", $string);
    }

    public function bytesToStr($bytes)
    {
        array_unshift($bytes,'C'.count($bytes));

        return call_user_func_array('pack', $bytes);
    }

    public function add(...$a)
    {
        $sum = array_sum($a);
        $sum > 0xFFFFFFFF && $sum &= 0xFFFFFFFF;

        return $sum;
    }

    public function getHex($number, $count = 8)
    {
        return str_pad($number, $count, "0", STR_PAD_LEFT);
    }

    public function generate($numBits = 256)
    {
        $value = gmp_random_bits($numBits);
        $mask = gmp_sub(gmp_pow(2, $numBits), 1);
        $integer = gmp_and($value, $mask);

        return $integer;
    }

    public function decHex($dec, $len = 0): string
    {
        if (!$dec instanceof \GMP) {
            $dec = gmp_init($dec, 10);
        }
        if (gmp_cmp($dec, 0) < 0) {
            throw new \Exception('Unable to convert negative integer to string');
        }

        $hex = gmp_strval($dec, 16);

        if (strlen($hex) % 2 != 0) {
            $hex = '0'.$hex;
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