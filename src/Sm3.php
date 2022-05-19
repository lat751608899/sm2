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
        $w = $w1 = [];
        $a = 0x7380166f;
        $b = 0x4914b2b9;
        $c = 0x172442d7;
        $d = 0xda8a0600;
        $e = 0xa96f30bc;
        $f = 0x163138aa;
        $g = 0xe38dee4d;
        $h = 0xb0fb0e4e;
        while (count($bytes) >= 64) {
            for ($i = 0; $i < 16; $i++) {
                $tmpStr = $this->bytesToStr(array_slice($bytes, 4 * $i, 4));
                $w[$i] = @unpack('N', $tmpStr)[1];
            }
            for ($i = 16; $i < 68; $i++) {
                $w13 = $this->leftRotate($w[$i - 13], 7);
                $p1 = $w[$i - 16] ^ $w[$i - 9] ^ $this->leftRotate($w[$i - 3], 15);
                $w[$i] = $this->p1($p1) ^ $w13 ^ $w[$i - 6];
            }
            for ($i = 0; $i < 64; $i++) {
                $w1[$i] = $w[$i] ^ $w[$i + 4];
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
                $a1 = $this->leftRotate($i < 16 ? 0x79cc4519 : 0x7a879d8a, $i);
                $a12 = $this->leftRotate($A, 12);
                $SS1 = $this->leftRotate($this->add($a12, $E, $a1), 7);
                $SS2 = $SS1 ^ $this->leftRotate($A, 12);
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
            $a ^= $A;
            $b ^= $B;
            $c ^= $C;
            $d ^= $D;
            $e ^= $E;
            $f ^= $F;
            $g ^= $G;
            $h ^= $H;
            $bytes = array_splice($bytes, 64);
        }

        $array = compact('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h');

        return $this->bytesToHex($array);
    }

    protected function ff0($x, $y, $z)
    {
        return $x ^ $y ^ $z;
    }

    protected function gg0($x, $y, $z)
    {
        return $x ^ $y ^ $z;
    }

    protected function ff1($x, $y, $z)
    {
        return ($x & $y) | ($x & $z) | ($y & $z);
    }

    protected function gg1($x, $y, $z)
    {
        return ($x & $y) | (~$x & $z);
    }

    protected function p0($x)
    {
        return $x ^ $this->leftRotate($x, 9) ^ $this->leftRotate($x, 17);
    }

    protected function p1($x)
    {
        return $x ^ $this->leftRotate($x, 15) ^ $this->leftRotate($x, 23);
    }
}
