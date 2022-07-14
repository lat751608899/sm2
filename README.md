### 使用(how to use)
* 可以使用 sm2 加解密， sm3
* composer require latzou/sm2
* php需要安装 gmp 扩展
* 目前只支持未压缩的 sm2 加解密，以 04 开头的
* 加解密数据组合方式为 c1c3c2
* 加解密数据都是 16 进制编码的，其他 Asn.1 格式的数据需自行解析
```
// 针对密钥是 Asn.1 格式的
$data = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
$key = base64_decode('MFkwEwYHKoZIzj0CAQYIKoEcz1UBgi0DQgAEShQSLl+hSiiJVcUjn6kVmhpCClh0
9RmCEUaKNMOXglHs0BTw1JITOZckfahMn/KHeop+9ubzPEB8fqdehWzzuQ==');
// sm2 加密
$pubKey = new \Lat\Ecc\PublicKey();
$pubKey->parse($key);
$sm2 = new \Lat\Ecc\Sm2();
$res = $sm2->pubEncrypt($pubKey, $data);

$key = base64_decode('MIGTAgEAMBMGByqGSM49AgEGCCqBHM9VAYItBHkwdwIBAQQgmKp8uBbpJhZCXliV
xksD3oM5H1oyDt84MNxiwVN6BAigCgYIKoEcz1UBgi2hRANCAARKFBIuX6FKKIlV
xSOfqRWaGkIKWHT1GYIRRoo0w5eCUezQFPDUkhM5lyR9qEyf8od6in725vM8QHx+
p16FbPO5');
// sm2 解密
$privKey = new \Lat\Ecc\PrivateKey();
$privKey->parse($key);
$a = $sm2->decrypt($privKey, $res);

// 针对密钥是解出来的16进制数据
// sm2 加密
$data = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
$key = '0451844d8f4e8881806f27169142f7e22932245af841d4b6c705e8e2b3adbb0041616dd0968910e76df6c4f6eb1d6f0ef11188b54bb09f47aa925c15d4ab13c119';
$sm2 = new \Lat\Ecc\Sm2();
$pubKey = new \Lat\Ecc\PublicKey();
$pubKey->parseUncompressedPoint($key);
$res = $sm2->pubEncrypt($pubKey, $data);

// sm2 解密
$privKey = new \Lat\Ecc\PrivateKey();
$privKey->setKey('0f3e3fb2b68197851656e805ddb9825b21b50d5e215894ca5dd5781fe4c4bea8');
$a = $sm2->decrypt($privKey, $res);
```
