<?php
$passphrase = 'change_me_to_secure_passphrase';
$dir = __DIR__ . '/config/jwt';

if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

$config = [
    'digest_alg'       => 'sha256',
    'private_key_bits' => 4096,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
];

$res = openssl_pkey_new($config);
if (!$res) {
    echo "Erreur OpenSSL: " . openssl_error_string() . PHP_EOL;
    exit(1);
}

openssl_pkey_export($res, $privateKey, $passphrase);
file_put_contents($dir . '/private.pem', $privateKey);

$pubKey = openssl_pkey_get_details($res)['key'];
file_put_contents($dir . '/public.pem', $pubKey);

echo "Clés JWT générées avec succès !" . PHP_EOL;
echo "  private.pem -> config/jwt/private.pem" . PHP_EOL;
echo "  public.pem  -> config/jwt/public.pem" . PHP_EOL;