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
     * 签名类
     *
     * @var Signer\SignerInterface[]
     */
    protected $signers = [];

    /**
     * 支付类实例 WebPay|WapPay
     *
     * @var array
     */
    protected $payInstances = [];

    /**
     * 新建实例
     *
     * @param string $partner
     */
    public function __construct($partner)
    {
        $this->partner = $partner;
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
     * 获取WebPay实例
     *
     * @return WebPay
     */
    public function getWebPay()
    {
        $key = __METHOD__;
        if (!isset($this->payInstances[$key])) {
            $this->payInstances[$key] = new WebPay($this);
        }
        return $this->payInstances[$key];
    }

    /**
     * 获取WapPay实例
     *
     * @return WapPay
     */
    public function getWapPay()
    {
        $key = __METHOD__;
        if (!isset($this->payInstances[$key])) {
            $this->payInstances[$key] = new WapPay($this);
        }
        return $this->payInstances[$key];
    }

    /**
     * 生成Alipay实例
     *
     * @param string $partner
     * @param array $signers
     * @return static
     */
    public static function create($partner, $signers = [])
    {
        $alipay = new static($partner);
        foreach ((array)$signers as $signer) {
            $alipay->addSigner($signer);
        }
        return $alipay;
    }

}