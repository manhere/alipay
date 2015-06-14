<?php

namespace Foccy\Alipay\Signer;


class RSASigner implements SignerInterface
{

    protected $partnerKeyPath;

    protected $alipayKeyPath;

    public function __construct($partnerKeyPath, $alipayKeyPath)
    {
        $this->partnerKeyPath = $partnerKeyPath;
        $this->alipayKeyPath = $alipayKeyPath;
    }


    /**
     * Sign the raw data.
     *
     * @param $raw
     * @return string
     */
    public function sign($raw)
    {
        $keyContent = file_get_contents($this->partnerKeyPath);
        $key = openssl_get_privatekey($keyContent);
        openssl_sign($raw, $sign, $key);
        openssl_free_key($key);
        $sign = base64_encode($sign);
        return $sign;
    }

    /**
     * Verify the raw data.
     *
     * @param string $raw
     * @param string $sign
     * @return bool
     */
    public function verify($raw, $sign)
    {
        $sign = base64_decode($sign);
        $keyContent= file_get_contents($this->alipayKeyPath);
        $key = openssl_get_publickey($keyContent);
        $verify = openssl_verify($raw, $sign, $key);
        openssl_free_key($key);
        return $verify === 1;
    }

    /**
     * Get the sign type name.
     *
     * @return string
     */
    public function getSignType()
    {
        return 'RSA';
    }


}