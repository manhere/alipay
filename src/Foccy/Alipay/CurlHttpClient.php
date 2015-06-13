<?php

namespace Foccy\Alipay;


use Foccy\Alipay\Exception\HttpException;

class CurlHttpClient implements HttpClientInterface
{

    protected $CACertPath;

    public function __construct($CACertPath)
    {
        $this->CACertPath = $CACertPath;
    }

    /**
     * @param string $url
     * @param string $method
     * @param array $data
     * @return mixed
     * @throws Exception\HttpException
     */
    public function executeHttpRequest($url, $method = self::METHOD_GET, array $data = [])
    {
        $ch = curl_init();
        if ($method === self::METHOD_GET) {
            if ($data) {
                $query = $this->buildQuery($data);
                if (strpos($url, '?') !== false) {
                    $url .= $query;
                } else {
                    $url .= '?' . $query;
                }
            }
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CAINFO, $this->CACertPath);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $body = curl_exec($ch);
        $info = curl_getinfo($ch);
        $code = $info['http_code'];
        if ($code === 200) {
            return $body;
        } else {
            $error = curl_error($ch);
            $msg = curl_error($ch);
            throw new HttpException($code, sprintf('%s %s', $error, $msg));
        }
    }

    protected function buildQuery(array $params)
    {
        $segments = [];
        foreach ($params as $key => $value) {
            $segments[] = $key . '=' . urlencode($value);
        }
        return implode('&', $segments);
    }

}