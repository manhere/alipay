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
     * HTTP 通讯类
     *
     * @var HttpClientInterface
     */
    protected $httpClient;

    /**
     * 新建实例
     *
     * @param string $partner
     * @param string $sellerEmail
     * @param string $CACertPath
     */
    public function __construct($partner, $sellerEmail, $CACertPath)
    {
        $this->partner = $partner;
        $this->sellerEmail = $sellerEmail;
        $this->CACertPath = $CACertPath;
    }

    /**
     * 获取合作伙伴ID
     *
     * @return string
     */
    public function getPartner()
    {
        return $this->partner;
    }

    /**
     * 获取卖家Email
     *
     * @return string
     */
    public function getSellerEmail()
    {
        return $this->sellerEmail;
    }

    /**
     * 获取支付宝证书路径
     *
     * @return string
     */
    public function getCACertPath()
    {
        return $this->CACertPath;
    }

    /**
     * 排序参数
     *
     * @param array $params
     * @return array
     */
    public function sortParams(array $params)
    {
        ksort($params);
        reset($params);
        return $params;
    }

    /**
     * 过滤签名参数和空参数
     *
     * @param array $params
     * @return array
     */
    public function filterParams(array $params)
    {
        unset($params['sign']);
        unset($params['sign_type']);
        return array_filter($params);
    }

    /**
     * 根据参数生成url
     *
     * @param array $params
     * @param bool $encoded
     * @return string
     */
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
     * 获取HTTP通讯实例
     *
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
     * 设置HTTP通讯实例
     *
     * @param HttpClientInterface $httpClient
     */
    public function setHttpClient(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

}