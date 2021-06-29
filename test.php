<?php

require "vendor/autoload.php";

$data = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
$key = base64_decode('MFkwEwYHKoZIzj0CAQYIKoEcz1UBgi0DQgAEShQSLl+hSiiJVcUjn6kVmhpCClh0
9RmCEUaKNMOXglHs0BTw1JITOZckfahMn/KHeop+9ubzPEB8fqdehWzzuQ==');
$pubKey = new \Lat\Ecc\PublicKey();
$pubKey->parse($key);
$sm2 = new \Lat\Ecc\Sm2();
$res = $sm2->pubEncrypt($pubKey, $data);

$key = base64_decode('MIGTAgEAMBMGByqGSM49AgEGCCqBHM9VAYItBHkwdwIBAQQgmKp8uBbpJhZCXliV
xksD3oM5H1oyDt84MNxiwVN6BAigCgYIKoEcz1UBgi2hRANCAARKFBIuX6FKKIlV
xSOfqRWaGkIKWHT1GYIRRoo0w5eCUezQFPDUkhM5lyR9qEyf8od6in725vM8QHx+
p16FbPO5');
$privKey = new \Lat\Ecc\PrivateKey();
$privKey->parse($key);
$a = $sm2->decrypt($privKey, $res);
var_dump($a);