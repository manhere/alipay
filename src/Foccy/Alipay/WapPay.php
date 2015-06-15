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
     * @var Signer\SignerInterface
     */
    protected $signer;

    /**
     * Create a new instance.
     *
     * @param Alipay $alipay
     * @param SignerInterface $signer
     */
    public function __construct(Alipay $alipay, SignerInterface $signer)
    {
        $this->alipay = $alipay;
        $this->signer = $signer;
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
     * @throws AlipayException
     */
    public function createPaymentUrl($outTradeNo, $subject, $fee, $notifyUrl, $returnUrl)
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
        $sign = $this->signer->sign($this->alipay->createParamUrl($params));
        $params['sign'] = $sign;
        $params['sign_type'] = $this->signer->getSignType();
        return $this->gatewayUrl . $this->alipay->createParamUrl($params, true);
    }

    /**
     * Verify the return data.
     *
     * @param array $data
     * @return bool
     */
    public function verifyReturn(array $data)
    {
        if (empty($data) || !isset($data['sign'])) {
            return false;
        }
        $sign = $data['sign'];
        $isVerified = $this->signer->verify($this->alipay->createParamUrl($this->alipay->sortParams($this->alipay->filterParams($data))), $sign);
        if ($isVerified) {
            if (empty($data['notify_id'])) {
                return true;
            }
            $verify_url = $this->verifyUrl . "partner=" . $this->alipay->getPartner() . "&notify_id=" . $data["notify_id"];
            $responseTxt = $this->alipay->getHttpClient()->executeHttpRequest($verify_url);
            return (bool)preg_match("/true$/i",$responseTxt);
        }
        return false;
    }

    /**
     * Verify the notify data.
     *
     * @param array $data
     * @return bool
     */
    public function verifyNotify(array $data)
    {
        return $this->verifyReturn($data);
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

    /**
     * Set the parameter signer.
     *
     * @param SignerInterface $signer
     */
    public function setSigner(SignerInterface $signer)
    {
        $this->signer = $signer;
    }

    /**
     * Get the request token from the alipay gateway.
     *
     * @param array $params
     * @return string
     * @throws AlipayException
     */
    protected function getRequestToken(array $params)
    {
        $requestData = '<direct_trade_create_req><notify_url>' . $params['notify_url'] . '</notify_url><call_back_url>' . $params['return_url'] . '</call_back_url><seller_account_name>' . $params['seller_email'] . '</seller_account_name><out_trade_no>' . $params['out_trade_no'] . '</out_trade_no><subject>' . $params['subject'] . '</subject><total_fee>' . $params['total_fee'] . '</total_fee><merchant_url>' . $params['merchant_url'] . '</merchant_url></direct_trade_create_req>';
        $params = $this->alipay->sortParams([
            "service" => 'alipay.wap.trade.create.direct',
            "partner" => $params['partner'],
            "sec_id" => $params['sec_id'],
            "format" => $params['format'],
            "v"	=> $params['v'],
            "req_id"	=> $params['req_id'],
            "req_data"	=> $requestData,
            "_input_charset"	=> $params['_input_charset'],
        ]);
        $params['sign'] = $this->signer->sign($this->alipay->createParamUrl($params));
        $result = $this->alipay->getHttpClient()->executeHttpRequest($this->gatewayUrl, 'POST', $params);
        $resultParams = [];
        foreach (explode('&', urldecode($result)) as $data) {
            list($key, $value) = explode('=', $data, 2);
            $resultParams[$key] = $value;
        }
        if (isset($resultParams['res_data'])) {
            $doc = new \DOMDocument();
            $doc->loadXML($resultParams['res_data']);
            $token = $doc->getElementsByTagName('request_token')->item(0)->nodeValue;
            return $token;
        } else {
            throw new AlipayException($resultParams['res_error']);
        }
    }

}