<?php

namespace Foccy\Alipay\Signer;


class MD5Signer implements SignerInterface
{

    protected $key;

    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * @param $paramString
     * @return string
     */
    public function sign($paramString)
    {
        return md5($paramString . $this->key);
    }

    /**
     * @param string $paramString
     * @param string $sign
     * @return bool
     */
    public function verify($paramString, $sign)
    {
        $alipaySign = md5($paramString . $this->key);
        return $alipaySign === $sign;
    }

    /**
     * @return string
     */
    public function getSignType()
    {
        return 'MD5';
    }

}