<?php

namespace Extenbox\Notify\Contracts;

interface SmsDriver
{
    /**
     * ارسال پیامک معمولی
     */
    public function sendNormal(string|array $to, string $message): SmsResponse;

    /**
     * ارسال پیامک با قالب (Pattern)
     */
    public function sendPattern(string|array $to, string $patternCode, array $variables): SmsResponse;

    /**
     * دریافت نام درایور
     */
    public function getName(): string;

    /**
     * دریافت شماره فرستنده
     */
    public function getSender(): string;

    /**
     * تنظیم شماره فرستنده
     */
    public function setSender(string $sender): static;

    /**
     * تنظیم تنظیمات درایور
     */
    public function setConfig(array $config): static;
}
