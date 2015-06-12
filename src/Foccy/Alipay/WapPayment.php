<?php


namespace Foccy\Alipay;


class WapPayment
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
     * Create a new instance.
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

    protected function getRequestToken(array $params)
    {
        $requestData = '<direct_trade_create_req><notify_url>' . $params['notify_url'] . '</notify_url><call_back_url>' . $params['return_url'] . '</call_back_url><seller_account_name>' . $params['seller_email'] . '</seller_account_name><out_trade_no>' . $params['out_trade_no'] . '</out_trade_no><subject>' . $params['subject'] . '</subject><total_fee>' . $params['total_fee'] . '</total_fee><merchant_url>' . $params['merchant_url'] . '</merchant_url></direct_trade_create_req>';
        $params = Util::sortParams([
            "service" => 'alipay.wap.trade.create.direct',
            "partner" => $params['partner'],
            "sec_id" => $params['sec_id'],
            "format" => $params['format'],
            "v"	=> $params['v'],
            "req_id"	=> $params['req_id'],
            "req_data"	=> $requestData,
            "_input_charset"	=> $params['_input_charset'],
        ]);
        $params['sign'] = $this->signer->sign(Util::createParamUrl($params));
        $result = Util::getHttpClient()->executeHttpRequest($this->gatewayUrl, 'POST', $params);
        $para_split = explode('&', urldecode($result));
        $para_text = [];
        //把切割后的字符串数组变成变量与数值组合的数组
        foreach ($para_split as $item) {
            //获得第一个=字符的位置
            $nPos = strpos($item,'=');
            //获得字符串长度
            $nLen = strlen($item);
            //获得变量名
            $key = substr($item,0,$nPos);
            //获得数值
            $value = substr($item,$nPos+1,$nLen-$nPos-1);
            //放入数组中
            $para_text[$key] = $value;
        }
        $doc = new \DOMDocument();
        $doc->loadXML($para_text['res_data']);
        $para_text['request_token'] = $doc->getElementsByTagName( "request_token" )->item(0)->nodeValue;
        return $para_text['request_token'];
    }

    public function createPaymentUrl($outTradeNo, $subject, $fee, $productUrl, $notifyUrl, $returnUrl)
    {
        $params = array(
            'partner' => $this->partner,
            'seller_email' => $this->sellerEmail,
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
        $params = Util::sortParams([
            "service" => 'alipay.wap.auth.authAndExecute',
            "partner" => $params['partner'],
            "sec_id" => $params['sec_id'],
            "format" => $params['format'],
            "v"	=> $params['v'],
            "req_id" => $params['req_id'],
            "req_data" => $reqData,
            "_input_charset" => $params['_input_charset'],
        ]);
        $sign = $this->signer->sign(Util::createParamUrl($params));
        $params['sign'] = $sign;
        return $this->gatewayUrl . Util::createParamUrl($params, true);
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