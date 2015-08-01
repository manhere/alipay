<?php


namespace Foccy\Alipay;


use Foccy\Alipay\Exception\AlipayException;
use Foccy\Alipay\Pay\PayComposer;
use Foccy\Alipay\Signer\SignerInterface;
use Foccy\Alipay\Verifier\Verifier;

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
     * PC网页支付
     *
     * @param string $outTradeNo
     * @param string $subject
     * @param string $fee
     * @param string $notifyUrl
     * @param string $returnUrl
     * @return PayComposer
     */
    public function createWebPay($outTradeNo, $subject, $fee, $notifyUrl, $returnUrl)
    {
        $params = array(
            'service' =>'create_direct_pay_by_user',
            'payment_type' =>'1',
            '_input_charset' =>'utf-8',
            'notify_url' => $notifyUrl,
            'return_url' => $returnUrl,
            'out_trade_no' => $outTradeNo,
            'subject' => $subject,
            'total_fee' => $fee,
        );
        $pay = new PayComposer($this, 'https://mapi.alipay.com/gateway.do?');
        $pay->add($params);
        return $pay;
    }

    /**
     * Wap支付
     *
     * @param string $outTradeNo
     * @param string $subject
     * @param string $fee
     * @param string $notifyUrl
     * @param string $returnUrl
     * @return PayComposer
     */
    public function createWapPay($outTradeNo, $subject, $fee, $notifyUrl, $returnUrl)
    {
        $params = array(
            'service' =>'alipay.wap.create.direct.pay.by.user',
            'payment_type' =>'1',
            '_input_charset' =>'utf-8',
            'notify_url' => $notifyUrl,
            'return_url' => $returnUrl,
            'out_trade_no' => $outTradeNo,
            'subject' => $subject,
            'total_fee' => $fee,
        );
        $pay = new PayComposer($this, 'https://mapi.alipay.com/gateway.do?');
        $pay->add($params);
        return $pay;
    }

    /**
     * 客户端支付
     *
     * @param string $outTradeNo
     * @param string $subject
     * @param string $body
     * @param string $fee
     * @param string $notifyUrl
     * @return PayComposer
     */
    public function createMobilePay($outTradeNo, $subject, $body, $fee, $notifyUrl)
    {
        $params = array(
            'service' =>'mobile.securitypay.pay',
            'payment_type' =>'1',
            '_input_charset' =>'utf-8',
            'notify_url' => $notifyUrl,
            'out_trade_no' => $outTradeNo,
            'subject' => $subject,
            'body' => $body,
            'total_fee' => $fee,
        );
        $pay = new PayComposer($this);
        $pay->add($params);
        return $pay;
    }

    /**
     * 生成支付通知验证实例
     *
     * @param array $params
     * @return Verifier
     */
    public function createVerifier(array $params)
    {
        return new Verifier($this, $params);
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