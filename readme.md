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

$signer = new RSASigner($myPrivateKeyPath, $alipayPublicKeyPath)
```


## 配置
```php
use Foccy\Alipay\Alipay;

$alipay = new Alipay('2088...');
```

## 生成支付链接

### 普通网页版
```php
use Foccy\Alipay\WebPay;

$webPay = new WebPay($alipay, $signer);

$url = $webPay->createPaymentUrl('order_number', 'product_name', '0.01', 'http://www.foo.com/notify.php', 'http://www.foo.com/return.php', 'CMB');
```

### 手机网页版
```php
use Foccy\Alipay\WapPay;

$wapPay = new WapPay($alipay, $signer);

$url = $wapPay->createPaymentUrl('order_number', 'product_name', '0.01', 'http://www.foo.com/notify.php', 'http://www.foo.com/return.php');
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