<?php


namespace Foccy\Alipay;


class Util
{

    /**
     * @var HttpClientInterface
     */
    protected static $httpClient;

    public static function sortParams(array $params)
    {
        ksort($params);
        reset($params);
        return $params;
    }

    public static function filterParams(array $params)
    {
        unset($params['sign']);
        unset($params['sign_type']);
        return array_filter($params);
    }

    public static function createParamUrl(array $params, $encoded = false)
    {
        $combinedParams = [];
        foreach ($params as $key => $val) {
            $combinedParams[] = implode('=', [$key, $encoded ? urlencode($val) : $val]);
        }
        $url = implode('&', $combinedParams);
        return $url;
    }

    /**
     * @return HttpClientInterface
     */
    public static function getHttpClient()
    {
        throw new \RuntimeException(__METHOD__ . ' not implemented.');
        return self::$httpClient;
    }

    /**
     * @param HttpClientInterface $httpClient
     */
    public static function setHttpClient(HttpClientInterface $httpClient)
    {
        self::$httpClient = $httpClient;
    }

}