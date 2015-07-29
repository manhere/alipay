<?php


namespace Foccy\Alipay;


use Foccy\Alipay\Exception\AlipayException;
use Foccy\Alipay\Signer\SignerInterface;

class Alipay
{

    /**
     * 合作伙伴 ID '2000202202020'
     *
     * @var string
     */
    protected $partner;

    /**
     * HTTP 通讯类
     *
     * @var HttpClientInterface
     */
    protected $httpClient;

    /**
     * 签名类
     *
     * @var Signer\SignerInterface[]
     */
    protected $signers = [];

    /**
     * 新建实例
     *
     * @param string $partner
     * @param SignerInterface[] $signers
     */
    public function __construct($partner, $signers = [])
    {
        $this->partner = $partner;
        foreach ((array)$signers as $signer) {
            $this->addSigner($signer);
        }
    }

    /**
     * Add a signer.
     *
     * @param SignerInterface $signer
     * @return $this
     */
    public function addSigner(SignerInterface $signer)
    {
        $this->signers[$signer->getSignType()] = $signer;
        return $this;
    }

    /**
     * Get the signer by signer type.
     *
     * @param string $signType
     * @return SignerInterface
     * @throws AlipayException
     */
    public function getSigner($signType)
    {
        if (isset($this->signers[$signType])) {
            return $this->signers[$signType];
        }
        throw new AlipayException(sprintf('Signer type [%s] not found.', $signType));
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
            $this->httpClient = new CurlHttpClient();
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