```php
use Foccy\Alipay\Alipay;
use Foccy\Alipay\Signer\MD5Signer;
use Foccy\Alipay\Signer\RSASigner;

$alipay = new Alipay('2088....');

```

## md5 加密方式
```php
$md5Signer = new MD5Signer('a2d9bm4jfk0slemvpaq23');
$alipay->addSigner($md5Signer);
```

## rsa 加密方式
```php
$rsaSigner = new RSASigner(__DIR__ . '/my_private_key', __DIR__ . '/alipay_pub_key');
$alipay->addSigner($rsaSigner);
```

## PC网页支付
```php
$webPay = $alipay->createWebPay('out_order_number', 'product_name', '0.01', 'http://www.example.com/payNotify', 'http://www.example.com/payReturn');
```

### 生成支付宝支付URL
```php
echo $webPay->compose();
```

### 设置网银支付银行
```php
$webPay->set('defaultbank', 'CMB');
echo $webPay->compose(); // 生成通过支付宝跳转到网银支付URL
```

### 以RSA加密方式生成支付URL
```php
use Foccy\Alipay\Signer\SignerInterface;
echo $webPay->compose(SignerInterface::TYPE_RSA);
```

## WAP网页支付
```php
$wapPay = $alipay->createWapPay('out_order_number', 'product_name', '0.01', 'http://www.example.com/payNotify', 'http://www.example.com/payReturn');

echo $wapPay->compose(); // 生成支付宝支付URL
```

## 手机客户端支付
```php
$mobilePay = $alipay->createMobilePay('out_order_number', 'foo', 'body', '0.01', 'http://www.exmaple.com/payNotify');

// 手机客户端只支持 RSA 签名
echo $mobilePay->compose(SignerInterface::TYPE_RSA);
```

## 验证 return 或 notify 通知
```php
$parameters = $_POST; // $parameters = $_GET;
$verifier = $alipay->createVerifier($parameters);
if ($verifier->verify()) {
    switch ($verifier->getParam('trade_status')) {
        case 'TRADE_FINISHED':
        case 'TRADE_SUCCESS':
            // 支付成功, 处理业务逻辑
            break;
        default:
            // 未能支付成功
            break;
    }
    // 通过验证
} else {
    // 验证失败
}
```