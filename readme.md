# 使用方法

## 签名

### MD5方式
```php
use Foccy\Alipay\Signer\MD5Signer;

$signer = new MD5Signer('5asd1as849xnakdlwe834324n24n3');
```

### RSA方式
```php
use Foccy\Alipay\Signer\RSASigner;

$signer = new RSASigner($myPrivateKeyPath, $alipayPublicKeyPath);
```


## 配置
```php
use Foccy\Alipay\Alipay;
use Foccy\Alipay\Signer\MD5Signer;
use Foccy\Alipay\Signer\RSASigner;

$md5Signer = new MD5Signer('5asd1as849xnakdlwe834324n24n3');
// 配置md5签名方式
$alipay = new Alipay('2088...', $md5Signer);

$rsaSigner = new RSASigner($myPrivateKeyPath, $alipayPublicKeyPath);
// 增加rsa签名方式
$alipay->addSigner($rsaSigner);

```
此时，配置类Alipay的实例拥有md5和rsa两种签名方式。

## 生成支付链接

### 普通网页版

生成支付链接接口
```php
public function createPaymentUrl($outTradeNo, $subject, $fee, $notifyUrl, $returnUrl, $bank = '', $signType = SignerInterface::TYPE_MD5);
```

```php
use Foccy\Alipay\WebPay;
use Foccy\Alipay\Signer\SignerInterface;

$webPay = new WebPay($alipay);

// 使用CMB银行支付，使用rsa签名方式
$url = $webPay->createPaymentUrl('order_number', 'product_name', '0.01', 'http://www.foo.com/notify.php', 'http://www.foo.com/return.php', 'CMB', SignerInterface::TYPE_RSA);
```

### 手机网页版
生成支付链接接口
```php
public function createPaymentUrl($outTradeNo, $subject, $fee, $notifyUrl, $returnUrl, $signType = SignerInterface::TYPE_MD5);
```
```php
use Foccy\Alipay\WapPay;
use Foccy\Alipay\Signer\SignerInterface;

$wapPay = new WapPay($alipay);

$url = $wapPay->createPaymentUrl('order_number', 'product_name', '0.01', 'http://www.foo.com/notify.php', 'http://www.foo.com/return.php', SignerInterface::TYPE_RSA);
```

## 验证通知
```php
$data = $_GET; // $data = $_POST;
if ($webPay->verify($data)) {
    // 同步或异步通知数据通过验证
}
if ($wapPay->verify($data)) {
    // 同步或异步通知数据通过验证
}
```