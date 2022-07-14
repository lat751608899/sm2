<?php

namespace Lat\Ecc;

use FG\ASN1\ASNObject;
use FG\ASN1\Identifier;

class PublicKey
{
    const X509_ECDSA_OID = "1.2.840.10045.2.1"; // x509 证书 oid
    const SECP_256SM2_OID = '1.2.156.10197.1.301'; // sm2 oid

    /** @var Point */
    protected $point;

    /**
     * @return mixed
     */
    public function getPoint()
    {
        return $this->point;
    }

    /**
     * @param mixed $point
     */
    public function setPoint(Point $point)
    {
        $this->point = $point;
    }


    /**
     * 公钥格式
    SEQUENCE {
        SEQUENCE {
            OBJECT IDENTIFIER (1.2.840.10045.2.1)
            OBJECT IDENTIFIER (1.2.156.10197.1.301)
        }
        BIT STRING (坐标点)
    }
     * @param string $binaryData
     * @return Point
     * @throws \FG\ASN1\Exception\ParserException
     */
    public function parse($binaryData)
    {
        $asnObject = ASNObject::fromBinary($binaryData);
        if ($asnObject->getType() !== Identifier::SEQUENCE) {
            throw new \RuntimeException('Invalid data.');
        }

        $children = $asnObject->getChildren();
        if (count($children) != 2) {
            throw new \RuntimeException('Invalid data.');
        }

        if (count($children[0]->getChildren()) != 2) {
            throw new \RuntimeException('Invalid data.');
        }

        if ($children[0]->getChildren()[0]->getType() !== Identifier::OBJECT_IDENTIFIER) {
            throw new \RuntimeException('Invalid data.');
        }

        if ($children[0]->getChildren()[1]->getType() !== Identifier::OBJECT_IDENTIFIER) {
            throw new \RuntimeException('Invalid data.');
        }

        if ($children[1]->getType() !== Identifier::BITSTRING) {
            throw new \RuntimeException('Invalid data.');
        }

        $oid = $children[0]->getChildren()[0];
        $curveOid = $children[0]->getChildren()[1];
        $encodedKey = $children[1];
        if ($oid->getContent() !== self::X509_ECDSA_OID) {
            throw new \RuntimeException('Invalid data: non X509 data.');
        }

        if ($curveOid->getContent() !== self::SECP_256SM2_OID) {
            throw new \RuntimeException('Invalid data: non sm2 data.');
        }
        list($x, $y) = $this->parseUncompressedPoint($encodedKey->getContent());
        $this->setPoint(new Point($x, $y));
    }

    public function parseUncompressedPoint($data)
    {
        if (substr($data, 0, 2) != '04') {
            throw new \InvalidArgumentException('Invalid data: only uncompressed keys are supported.');
        }
        $data = substr($data, 2);
        $dataLength = strlen($data);
        if ($dataLength != 128) {
            throw new \InvalidArgumentException('Invalid Public Key length');
        }
        $x = gmp_init(substr($data, 0, $dataLength / 2), 16);
        $y = gmp_init(substr($data, $dataLength / 2), 16);
        $this->setPoint(new Point($x, $y)); //test
        
        return [$x, $y];
    }
}
