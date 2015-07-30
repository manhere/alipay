<?php


namespace Foccy\Alipay;


use Foccy\Alipay\Exception\AlipayException;
use Foccy\Alipay\Signer\SignerInterface;

class WebPay
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
     * @return string string
     * @param string $bank
     * @param string $signType
     * @throws AlipayException
     */
    public function createUrl($outTradeNo, $subject, $fee, $notifyUrl, $returnUrl, $bank = '', $signType = SignerInterface::TYPE_MD5)
    {
        $params = array(
            'service' =>'create_direct_pay_by_user',
            'partner' => $this->alipay->getPartner(),
            'payment_type' =>'1',
            '_input_charset' =>'utf-8',
            'notify_url' => $notifyUrl,
            'return_url' => $returnUrl,
            'seller_id' => $this->alipay->getPartner(),
            'out_trade_no' => $outTradeNo,
            'subject' => $subject,
            'total_fee' => $fee,
        );
        if ($bank) {
            $params['defaultbank'] = $bank;
        }
        $utils = Utils::getInstance();
        $params = $utils->sortParams($params);
        $params = $utils->filterParams($params);
        $sign = $this->alipay->getSigner($signType)->sign($utils->createParamUrl($params));
        $params['sign'] = $sign;
        $params['sign_type'] = $signType;
        return $this->gatewayUrl . $utils->createParamUrl($params, true);
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
        $utils = Utils::getInstance();
        $sign = $data['sign'];
        $signType = $data['sign_type'];
        $isVerified = $this->alipay->getSigner($signType)->verify($utils->createParamUrl($utils->sortParams($utils->filterParams($data))), $sign);
        if ($isVerified) {
            if (empty($data['notify_id'])) {
                return true;
            }
            $verify_url = $this->verifyUrl . "partner=" . $this->alipay->getPartner() . "&notify_id=" . $data["notify_id"];
            $responseTxt = $utils->getHttpClient()->executeHttpRequest($verify_url);
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