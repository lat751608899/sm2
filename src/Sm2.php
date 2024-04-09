<?php

namespace Lat\Ecc;

class Sm2
{
    use Util;

    protected $sm3;

    public function __construct()
    {
        $this->sm3 = new Sm3();
    }

    /**
     *
     * @param PublicKey $publicKey
     * @param $data
     * @param $mode 1= C1C3C2  2=C1C2C3
     * @return string
     * @throws \Exception
     */
    public function pubEncrypt(PublicKey $publicKey, $data, $mode = 1)
    {
        $point = $publicKey->getPoint();
        $t = '';
        while (!$t){
            $k = $this->generate(); // 随机数
            //dump($k);
            //$k = gmp_init('104953050056413721046883757640585885959005820148174417356964987920496726278110',10);
            $kG = $point->mul($k);
            $x1 = $this->decHex($kG->getX(), 64);
            $y1 = $this->decHex($kG->getY(), 64);
            $c1 = $x1.$y1;
            $kPb = $point->mul($k, false);
            $x2 = gmp_strval($kPb->getX(), 16);
            $y2 = gmp_strval($kPb->getY(), 16);
            $x2 = pack('H*', str_pad($x2, 64, 0,STR_PAD_LEFT));
            $y2 = pack('H*', str_pad($y2, 64, 0,STR_PAD_LEFT));
            $t = $this->kdf($x2.$y2, strlen($data));
        }
        $c2 = gmp_xor(gmp_init($t, 16), $this->strToInt($data));
        $c2 = $this->decHex($c2, strlen($data) * 2);
        $c3 = $this->sm3->sm3($x2 . $data . $y2);
        if ($mode == 1) {
            $encryptData = "04".$c1.$c3.$c2;
        } else {
            $encryptData = "04".$c1.$c2.$c3;
        }

        return $encryptData;
    }

    protected function kdf($z, $klen)
    {
        $res = '';
        $ct = 1;
        $j = ceil($klen / 32);
        for ($i = 0; $i < $j; $i++) {
            $hex = $this->sm3->sm3($z . pack('N', $ct));
            if ($i + 1 == $j && $klen % 32 != 0) {  // 最后一个 且 $klen/$v 不是整数
                $res .= substr($hex, 0, ($klen % 32) * 2); // 16进制比byte长度少一半 要乘2
            } else {
                $res .= $hex;
            }
            $ct++;
        }

        return $res;
    }

    public function decrypt(PrivateKey $privateKey,$data, $mode = 1)
    {
        $decodeData = substr($data, 2);
        // 取出 c1
        $c1 = substr($decodeData, 0,128); // 转成16进制后 点数据长度要乘以2
        $x1 = substr($c1, 0,64);
        $y1 = substr($c1, 64);
        $dbC1 = (new Point(gmp_init($x1, 16), gmp_init($y1,16)))->mul($privateKey->getKey(), false);
        $x2 = $this->decHex($dbC1->getX(), 64);
        $y2 = $this->decHex($dbC1->getY(), 64);
        $len = strlen($decodeData) - 128 - 64;
        $t = $this->kdf($x2 . $y2, $len / 2);  // 转成16进制后 字符长度要除以2
        if ($mode == 1) { // C1C3C2
            $c2 = substr($decodeData, -$len);
            $c3 = substr($decodeData, 128,64); // 验证hash数据
        } else { // C1C2C3
            $c3 = substr($decodeData, -64);
            $c2 = substr($decodeData, 128, $len);
        }
        $m1 = $this->decHex(gmp_xor(gmp_init($t, 16), gmp_init($c2, 16)));
        $u = $this->sm3->sm3($x2.$m1.$y2, true);
        if(strtoupper($u) != strtoupper($c3)){
            throw new \Exception("error decrypt data");
        }

        return pack("H*",$m1);
    }
}
