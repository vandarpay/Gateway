<?php

namespace Vandar\Gateway\Saman;

use Vandar\Gateway\Exceptions\BankException;

class SamanException extends BankException
{

    public static $errors = array(
        "OK" => "پرداخت با موفقیت انجام شد",
        'CanceledByUser' => 'تراکنش توسط خریدار کنسل شد',
        'Failed'=>'پرداخت انجام نشد',
        'SessionIsNull' => ' کاربر در بازه زمانی تعیین شده پاسخی ارسال نکرده است',
        'InvalidParameters' => 'پارامترهای ارسالی نامعتبر است',
        'MerchantIpAddressIsInvalid' => 'آدرس سرور پذیرنده نامعتبر است )در پرداخت های بر پایه توکن(',
        'TokenNotFound' => 'توکن ارسال شده یافت نشد',
        
        'InvalidAmount' => 'مبلغ سند برگشتی از مبلغ تراکنش اصلی بیشتر است',
        'InvalidTransaction' => 'درخواست برگشت تراکنش رسیده است در حالی که تراکنش اصلی پیدا نمی شود',
        'InvalidCardNumber' => 'شماره کارت اشتباه است',
        'NoSuchIssuer' => 'چنین صادر کننده کارتی وجود ندارد',
        'ExpiredCardPickUp' => 'از تاریخ انقضای کارت گذشته است و کارت دیگر معتبر نیست',
        'IncorrectPIN' => 'رمز کارت (PIN) اشتباه وارد شده است',
        'NoSufficientFunds' => 'موجودی به اندازه کافی در حساب شما نیست',
        'IssuerDownSlm' => 'سیستم کارت بانک صادر کننده فعال نیست',
        'TMEError' => 'خطا در شبکه بانکی',
        'ExceedsWithdrawalAmountLimit' => 'مبلغ بیش از سقف برداشت است',
        'TransactionCannotBeCompleted' => 'امکان سند خوردن وجود ندارد',
        'AllowablePINTriesExceededPickUp' => 'رمز کارت (PIN) 3 مرتبه اشتباه وارد شده است در نتیجه کارت شما غیر فعال خواهد شد',
        'ResponseReceivedTooLate' => 'تراکنش در شبکه بانکی Timeout خورده است',
        'SuspectedFraudPickUp' => 'فیلد CV2V و یا فیلد ExpDate اشتباه وارد شده و یا اصلا وارد نشده است',


        -1 => "خطای داخلی شبکه مالی",
        -2 => "سپرده ها برابر نیستند",
        -3 => "ورودی ها حاوی کاراکترهای غیر مجاز می باشند",
        -4 => "کلمه عبور یا کد فروشنده اشتباه می باشد",
        -5 => "Database Exception",
        -6 => "سند قبلا برگشت کامل یافته است",
        -7 => "رسید دیجیتالی تهی است",
        -8 => "طول ورودی ها بیش از حد مجاز است",
        -9 => "وجود کاراکتر های غیر مجاز در مبلغ برگشتی",
        -10 => "رسید دیجیتالی حاوی کاراکترهای غیر مجاز است",
        -11 => "طول ورودی ها کمتر از حد مجاز است",
        -12 => "مبلغ برگشتی منفی است",
        -13 => "مبلغ برگشتی برای برگشت جزئی، بیش از مبلغ برگشت نخورده رسید دیجیتالی است",
        -14 => "چنین تراکنشی تعریف نشده است",
        -15 => "مبلغ برگشتی به صورت اعشاری داده شده است",
        -16 => "خطای داخلی سیستم",
        -17 => "برگشت زدن جزئی تراکنشی که با کارت بانکی غیر از بانک سامان انجام پذیرفته است",
        -18 => "IP Address فروشنده نا معتبر است"
    );

    public function __construct($errorRef)
    {
        $this->errorRef = $errorRef;

        parent::__construct(@self::$errors[$this->errorRef].' ('.$this->errorRef.')', intval($this->errorRef));
    }
}
