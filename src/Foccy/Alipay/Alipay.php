<?php


namespace Foccy\Alipay;


class Alipay
{

    /**
     * 合作伙伴 ID '2000202202020'
     *
     * @var string
     */
    protected $partner;

    /**
     * 合作伙伴 e-mail foo@exmaple.com
     *
     * @var string
     */
    protected $sellerEmail;

    /**
     * 支付宝证书路径
     *
     * @var string
     */
    protected $CACertPath;

    public function __construct($partner, $sellerEmail, $CACertPath)
    {
        $this->partner = $partner;
        $this->sellerEmail = $sellerEmail;
        $this->CACertPath = $CACertPath;
    }

}