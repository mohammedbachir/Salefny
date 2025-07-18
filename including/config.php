<?php


use Chargily\ChargilyPay\Auth\Credentials;
use Chargily\ChargilyPay\ChargilyPay;
require __DIR__ . '/../vendor/autoload.php';
$credentials = new Credentials([
    "mode" => "test", // أو "live"
    "public" => "test_pk_e4UtJ11Xi2cKcneOxnOf1of2KfE4uK4wiVcbDvJS",
    "secret" => "test_sk_mVchQrWHRM0TOQ4mE8uC6NnCRdBLP1p4ojFMZnWx",
]);

$chargily_pay = new ChargilyPay($credentials);

// ✅ جلب الرصيد
$balance = $chargily_pay->balance()->get();
print_r($balance);

// ✅ جلب قائمة العملاء
$customers = $chargily_pay->customers()->all();
print_r($customers);

// ✅ جلب جميع روابط الدفع
$links = $chargily_pay->payment_links()->all();
print_r($links);

// ✅ جلب الفواتير (checkouts)
$checkouts = $chargily_pay->checkouts()->all();
print_r($checkouts);

// ✅ جلب المنتجات
$products = $chargily_pay->products()->all();
print_r($products);

// ✅ تحقق من Webhook
$webhook = $chargily_pay->webhook()->get();
print_r($webhook);
?>
