<?php


namespace Lat\Ecc;


class Sm3
{
    use Util;

    /**
     * sm3 密码杂凑函数
     * @param $data
     * @param bool $hex
     * @return array
     */
    public function sm3($data, $hex = false)
    {
        if ($hex) {  // 传的16进制数据
            $data = hex2bin($data);
        }
        $bytes = $this->strToBytes($data);
        $bytes[] = 128; // 补 1
        while (count($bytes) % 64 != 56) {
            $bytes[] = 0;
        }
        $pack = pack('n', strlen($data) * 8);
        $packBytes = $this->strToBytes($pack);
        if (count($packBytes) < 8) {
            $fill = array_fill(0, 8 - count($packBytes), 0);
            $packBytes = array_merge($fill, $packBytes);
        }
        $bytes = array_merge($bytes, $packBytes);
        $w = $w1 = array();
        $a = gmp_init('0x7380166f', 16);
        $b = gmp_init('0x4914b2b9', 16);
        $c = gmp_init('0x172442d7', 16);
        $d = gmp_init('0xda8a0600', 16);
        $e = gmp_init('0xa96f30bc', 16);
        $f = gmp_init('0x163138aa', 16);
        $g = gmp_init('0xe38dee4d', 16);
        $h = gmp_init('0xb0fb0e4e', 16);
        while (count($bytes) >= 64) {
            for ($i = 0; $i < 16; $i++) {
                $w[$i] = $this->bytesToInt(array_slice($bytes, 4 * $i, 4));
            }
            for ($i = 16; $i < 68; $i++) {
                $w13 = $this->leftRotate($w[$i - 13], 7);
                $p1 = gmp_xor(gmp_xor($w[$i - 16], $w[$i - 9]), $this->leftRotate($w[$i - 3], 15));
                //$p1 = $w[$i - 16] ^ $w[$i - 9] ^ $this->leftRotate($w[$i - 3], 15);
                //$w[$i] = $this->p1($p1) ^ $w13 ^ $w[$i - 6];
                $w[$i] = gmp_xor(gmp_xor($this->p1($p1), $w13), $w[$i - 6]);
            }
            for ($i = 0; $i < 64; $i++) {
//                $w1[$i] = $w[$i] ^ $w[$i + 4];
                $w1[$i] = gmp_xor($w[$i], $w[$i + 4]);
            }
            $A = $a;
            $B = $b;
            $C = $c;
            $D = $d;
            $E = $e;
            $F = $f;
            $G = $g;
            $H = $h;
            for ($i = 0; $i < 64; $i++) {
                $a1 = $this->leftRotate(gmp_init($i < 16 ? '0x79cc4519' : '0x7a879d8a', 16), $i);
                $a12 = $this->leftRotate($A, 12);
                $SS1 = $this->leftRotate($this->add($a12, $E, $a1), 7);
//                $SS2 = $SS1 ^ $this->leftRotate($A, 12);
                $SS2 = gmp_xor($SS1, $this->leftRotate($A, 12));
                if ($i < 16) {
                    $TT1 = $this->add($this->ff0($A, $B, $C), $D, $SS2, $w1[$i]);
                    $TT2 = $this->add($this->gg0($E, $F, $G), $H, $SS1, $w[$i]);
                } else {
                    $TT1 = $this->add($this->ff1($A, $B, $C), $D, $SS2, $w1[$i]);
                    $TT2 = $this->add($this->gg1($E, $F, $G), $H, $SS1, $w[$i]);
                }
                $D = $C;
                $C = $this->leftRotate($B, 9);
                $B = $A;
                $A = $TT1;
                $H = $G;
                $G = $this->leftRotate($F, 19);
                $F = $E;
                $E = $this->p0($TT2);
            }
            $a = gmp_xor($a, $A);
            $b = gmp_xor($b, $B);
            $c = gmp_xor($c, $C);
            $d = gmp_xor($d, $D);
            $e = gmp_xor($e, $E);
            $f = gmp_xor($f, $F);
            $g = gmp_xor($g, $G);
            $h = gmp_xor($h, $H);
            /*$a ^= $A;
            $b ^= $B;
            $c ^= $C;
            $d ^= $D;
            $e ^= $E;
            $f ^= $F;
            $g ^= $G;
            $h ^= $H;*/
            $bytes = array_splice($bytes, 64);
        }

        $array = compact('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h');

        return $this->bytesToHex($array);
    }

    protected function ff0($x, $y, $z)
    {
        return gmp_xor(gmp_xor($x, $y), $z);
        return $x ^ $y ^ $z;
    }

    protected function gg0($x, $y, $z)
    {
        return gmp_xor(gmp_xor($x, $y), $z);
        return $x ^ $y ^ $z;
    }

    protected function ff1($x, $y, $z)
    {
        return gmp_or(gmp_or(gmp_and($x, $y), gmp_and($x, $z)), gmp_and($y, $z));
        return ($x & $y) | ($x & $z) | ($y & $z);
    }

    protected function gg1($x, $y, $z)
    {
        return gmp_or(gmp_and($x, $y), gmp_and(gmp_neg(gmp_add($x, 1)), $z));
        return ($x & $y) | (~$x & $z);
    }

    protected function p0($x)
    {
        return gmp_xor(gmp_xor($x, $this->leftRotate($x, 9)), $this->leftRotate($x, 17));
        return $x ^ $this->leftRotate($x, 9) ^ $this->leftRotate($x, 17);
    }

    protected function p1($x)
    {
        return gmp_xor(gmp_xor($x, $this->leftRotate($x, 15)), $this->leftRotate($x, 23));
        return $x ^ $this->leftRotate($x, 15) ^ $this->leftRotate($x, 23);
    }
}
