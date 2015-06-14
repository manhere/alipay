## 使用方法
### 签名
```php
$signer = new \Foccy\Alipay\Signer\MD5Signer('5asd1as849xnakdlwe834324n24n3');
```

### 配置
```php
$alipay = new \Foccy\Alipay\Alipay(
    '2088...',
    'foo@example.com'
);
```

## 普通网页版
```php
$webPayment = new \Foccy\Alipay\DirectPayByUser(
    $alipay,
    $signer
);

$url = $webPayment->createPaymentUrl('order_number', 'product_name', '0.01', 'http://www.foo.com/notify.php', 'http://www.foo.com/return.php', 'bankname');
```

## 手机网站版
```php
$wapPayment = new \Foccy\Alipay\DirectWapPayByUser(
    $alipay,
    $signer
);

$url = $wapPayment->createPaymentUrl('order_number', 'product_name', '0.01', 'http://www.foo.com/notify.php', 'http://www.foo.com/return.php');
```