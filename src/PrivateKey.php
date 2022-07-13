<?php

namespace Lat\Ecc;

use FG\ASN1\ASNObject;
use FG\ASN1\Identifier;

class PrivateKey
{
    const X509_ECDSA_OID = "1.2.840.10045.2.1"; // x509 证书 oid
    const SECP_256SM2_OID = '1.2.156.10197.1.301'; // sm2 oid
    protected $key;
    protected $pubKey;

    /**
     * @param string $binaryData
     * @return Point
     * @throws \FG\ASN1\Exception\ParserException
     */
    public function parse($binaryData)
    {
        $asnObject = ASNObject::fromBinary($binaryData);
        $children = $asnObject->getChildren();
        if ($asnObject->getType() !== Identifier::SEQUENCE) {
            throw new \RuntimeException('Invalid data.');
        }
        /** @var Sequence $asnObject */
        if ($asnObject->getNumberofChildren() != 3) {
            throw new \RuntimeException('Invalid data.');
        }
        $oid = $children[1]->getContent()[1];
        $bin = hex2bin($children[2]->getContent());
        $otherAsn = ASNObject::fromBinary($bin);
        $otherChildren = $otherAsn->getChildren();
        $version = $otherChildren[0]; // 版本
        $this->setKey($otherChildren[1]->getContent());// 私钥
    }

    public function setKey($key)
    {
        $this->key = gmp_init($key, 16);  // 私钥;
    }

    public function getKey()
    {
        return $this->key;
    }

    protected function parseUncompressedPoint($data)
    {
        if (substr($data, 0, 2) != '04') {
            throw new \InvalidArgumentException('Invalid data: only uncompressed keys are supported.');
        }
        $data = substr($data, 2);
        $dataLength = strlen($data);

        $x = gmp_init(substr($data, 0, $dataLength / 2), 16);
        $y = gmp_init(substr($data, $dataLength / 2), 16);

        return [$x, $y];
    }

    public function getPublickKey()
    {
        if($this->pubKey){
            return $this->pubKey;
        }
        $point = new Point(gmp_init(0), gmp_init(0));
        $pubPoint = $point->mul($this->key, true);
        $pubKey = new PublicKey();
        $pubKey->setPoint($pubPoint);
        $this->pubKey = $pubKey;

        return $pubKey;
    }
}