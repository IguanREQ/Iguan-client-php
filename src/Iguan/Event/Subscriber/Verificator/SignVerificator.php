<?php

namespace Iguan\Event\Subscriber\Verificator;

use Iguan\Event\Subscriber\SubjectNotifyWay;
use phpseclib\Crypt\RSA;

/**
 * Class SignVerificator
 * Verify payload using RSA signature.
 *
 * @author Vishnevskiy Kirill
 */
class SignVerificator extends Verificator
{
    private $publicKey;

    /**
     * SignVerificator constructor.
     * @param string $publicKeyPath a file with RSA public key inside
     * @throws InvalidPublicKeyException
     */
    public function __construct($publicKeyPath)
    {
        if (empty($publicKeyPath) || !file_exists($publicKeyPath)) {
            throw new InvalidPublicKeyException("Public key ($publicKeyPath) is empty or does not exists.");
        }

        $this->publicKey = file_get_contents($publicKeyPath);
    }

    /**
     * @param SubjectNotifyWay $way
     * @return bool
     * @throws InvalidPublicKeyException
     */
    public function isVerified(SubjectNotifyWay $way)
    {
        $rsa = new RSA();
        if (!$rsa->loadKey($this->publicKey)) {
            throw new InvalidPublicKeyException('Unable to import public key.');
        }

        $rsa->setHash('sha256');
        $rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
        $rsa->setSignatureMode(RSA::SIGNATURE_PKCS1);

        $data = $way->getSignedContextData();
        $sign = $way->getSign();
        return $rsa->verify($data, $sign);
    }
}