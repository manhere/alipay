<?php


namespace Foccy\Alipay;


use Foccy\Alipay\Exception\AlipayException;
use Foccy\Alipay\Signer\SignerInterface;

class WapPayment
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
    protected $gatewayUrl = 'http://wappaygw.alipay.com/service/rest.htm?';

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

    public function createPaymentUrl($outTradeNo, $subject, $fee, $productUrl, $notifyUrl, $returnUrl)
    {
        $params = array(
            'partner' => $this->alipay->getPartner(),
            'seller_email' => $this->alipay->getSellerEmail(),
            'out_trade_no' => $outTradeNo,
            'subject' => $subject,
            'total_fee' => $fee,
            'merchant_url' => $productUrl,
            "sec_id" => $this->signer->getSignType(),
            "format" => 'xml',
            "v"	=> '2.0',
            "req_id" => date('Ymdhis'),
            "_input_charset" => 'utf-8',
            'notify_url'=> $notifyUrl,
            'return_url'=> $returnUrl,
        );
        $token = $this->getRequestToken($params);
        $reqData = '<auth_and_execute_req><request_token>' . $token . '</request_token></auth_and_execute_req>';
        $params = $this->alipay->sortParams([
            "service" => 'alipay.wap.auth.authAndExecute',
            "partner" => $params['partner'],
            "sec_id" => $params['sec_id'],
            "format" => $params['format'],
            "v"	=> $params['v'],
            "req_id" => $params['req_id'],
            "req_data" => $reqData,
            "_input_charset" => $params['_input_charset'],
        ]);
        $sign = $this->signer->sign($this->alipay->createParamUrl($params));
        $params['sign'] = $sign;
        return $this->gatewayUrl . $this->alipay->createParamUrl($params, true);
    }

    public function verifyReturn(array $data)
    {
        if(empty($data)) {
            return false;
        }

        if (isset($data['result']) && $data['result'] === 'success') {
            $sign = $data["sign"];
            $params = $this->alipay->filterParams($data);
            $params = $this->alipay->sortParams($params);
            $str = $this->alipay->createParamUrl($params);

            return $this->signer->verify($str, $sign);
        }
        return false;
    }

    public function verifyNotify(array $data)
    {
        if(empty($data)) {
            return false;
        }

        $sign = $data["sign"];
        $params = $this->alipay->filterParams($data);
        $verifiedParams = [];
        $verifiedParams['service'] = $params['service'];
        $verifiedParams['v'] = $params['v'];
        $verifiedParams['sec_id'] = $params['sec_id'];
        $verifiedParams['notify_data'] = $params['notify_data'];

        $str = $this->alipay->createParamUrl($verifiedParams);
        if ($this->signer->verify($str, $sign)) {
            $doc = new \DOMDocument();
            $doc->loadXML($data['notify_data']);

            $tradeStatus = $doc->getElementsByTagName('trade_status')->item(0)->nodeValue;

            if ($tradeStatus === 'TRADE_FINISHED' || $tradeStatus === 'TRADE_SUCCESS') {
                $notify_id = $doc->getElementsByTagName( "notify_id" )->item(0)->nodeValue;
                if (!empty($notify_id)) {
                    $verify_url = $this->verifyUrl . "partner=" . $this->alipay->getPartner() . "&notify_id=" . $notify_id;
                    $responseTxt = $this->alipay->getHttpClient()->executeHttpRequest($verify_url);
                    return preg_match("/true$/i",$responseTxt);
                }
                return true;
            }
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