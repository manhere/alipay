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

    /**
     * @var HttpClientInterface
     */
    protected $httpClient;

    public function __construct($partner, $sellerEmail, $CACertPath)
    {
        $this->partner = $partner;
        $this->sellerEmail = $sellerEmail;
        $this->CACertPath = $CACertPath;
    }

    /**
     * @return string
     */
    public function getPartner()
    {
        return $this->partner;
    }

    /**
     * @return string
     */
    public function getSellerEmail()
    {
        return $this->sellerEmail;
    }

    /**
     * @return string
     */
    public function getCACertPath()
    {
        return $this->CACertPath;
    }

    public function sortParams(array $params)
    {
        ksort($params);
        reset($params);
        return $params;
    }

    public function filterParams(array $params)
    {
        unset($params['sign']);
        unset($params['sign_type']);
        return array_filter($params);
    }

    public function createParamUrl(array $params, $encoded = false)
    {
        $combinedParams = [];
        foreach ($params as $key => $val) {
            $combinedParams[] = implode('=', [$key, $encoded ? urlencode($val) : $val]);
        }
        $url = implode('&', $combinedParams);
        return $url;
    }

    /**
     * @return HttpClientInterface
     */
    public function getHttpClient()
    {
        if (is_null($this->httpClient)) {
            $this->httpClient = new CurlHttpClient($this->CACertPath);
        }
        return $this->httpClient;
    }

    /**
     * @param HttpClientInterface $httpClient
     */
    public function setHttpClient(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

}