<?php


namespace Foccy\Alipay;


use Foccy\Alipay\Exception\AlipayException;
use Foccy\Alipay\Signer\SignerInterface;

class WapPay
{

    /**
     * @var Alipay
     */
    protected $alipay;

    /**
     * 支付宝支付网关地址.
     *
     * @var string
     */
    protected $gatewayUrl = 'https://mapi.alipay.com/gateway.do?';

    /**
     * 支付宝通知验证地址
     *
     * @var string
     */
    protected $verifyUrl = 'http://notify.alipay.com/trade/notify_query.do?';

    /**
     * Create a new instance.
     *
     * @param Alipay $alipay
     */
    public function __construct(Alipay $alipay)
    {
        $this->alipay = $alipay;
    }

    /**
     * Create a redirect url.
     *
     * @param string $outTradeNo
     * @param string $subject
     * @param string $fee
     * @param string $notifyUrl
     * @param string $returnUrl
     * @param string $signType
     * @return string string
     * @throws AlipayException
     */
    public function createPaymentUrl($outTradeNo, $subject, $fee, $notifyUrl, $returnUrl, $signType = SignerInterface::TYPE_MD5)
    {
        $params = array(
            'service' =>'alipay.wap.create.direct.pay.by.user',
            'payment_type' =>'1',
            '_input_charset' =>'utf-8',
            'notify_url' => $notifyUrl,
            'return_url' => $returnUrl,
            'partner' => $this->alipay->getPartner(),
            'seller_id' => $this->alipay->getPartner(),
            'out_trade_no' => $outTradeNo,
            'subject' => $subject,
            'total_fee' => $fee,
        );
        $params = $this->alipay->sortParams($params);
        $params = $this->alipay->filterParams($params);
        $sign = $this->alipay->getSigner($signType)->sign($this->alipay->createParamUrl($params));
        $params['sign'] = $sign;
        $params['sign_type'] = $signType;
        return $this->gatewayUrl . $this->alipay->createParamUrl($params, true);
    }

    /**
     * Verify the return data.
     *
     * @param array $data
     * @return bool
     */
    public function verify(array $data)
    {
        if (empty($data) || !isset($data['sign']) || !isset($data['sign_type'])) {
            return false;
        }
        $sign = $data['sign'];
        $signType = $data['sign_type'];
        $isVerified = $this->alipay->getSigner($signType)->verify($this->alipay->createParamUrl($this->alipay->sortParams($this->alipay->filterParams($data))), $sign);
        if ($isVerified) {
            if (empty($data['notify_id'])) {
                return true;
            }
            $verify_url = $this->verifyUrl . "partner=" . $this->alipay->getPartner() . "&notify_id=" . $data["notify_id"];
            $responseTxt = $this->alipay->getHttpClient()->executeHttpRequest($verify_url);
            return $responseTxt === 'true';
        }
        return false;
    }

    /**
     * Set the gateway url.
     *
     * @param string $gatewayUrl
     */
    public function setGatewayUrl($gatewayUrl)
    {
        $this->gatewayUrl = $gatewayUrl;
    }

    /**
     * Set the notification verify url.
     *
     * @param string $verifyUrl
     */
    public function setVerifyUrl($verifyUrl)
    {
        $this->verifyUrl = $verifyUrl;
    }

}