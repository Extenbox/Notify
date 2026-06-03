<?php

namespace Extenbox\Notify\Drivers;

use Extenbox\Notify\Contracts\SmsResponse;

/**
 * درایور پارس گرین
 * https://parsgreen.ir
 * 
 * وب‌سرویس SOAP-based
 */
class ParsGreen extends BaseDriver
{

    protected string $sendEndpoint = 'http://sms.parsgreen.ir/Api/SendSMS.asmx';
    protected string $profileEndpoint = 'http://sms.parsgreen.ir/Api/ProfileService.asmx';

    public function getName(): string
    {
        return 'parsgreen';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'text/xml; charset=utf-8',
        ];
    }

    public function sendNormal(string|array $to, string $message): SmsResponse
    {
        $phones = $this->normalizePhones($to);
        $signature = $this->config['signature'] ?? '';
        $sender = $this->getSender();

        try {
            $xml = $this->buildSendGroupSmsXml($signature, $phones, $message, $sender);

            $response = $this->client->post($this->sendEndpoint, [
                'headers' => array_merge($this->defaultHeaders(), [
                    'SOAPAction' => 'http://tempuri.org/SendGroupSmsSimple',
                ]),
                'body' => $xml,
            ]);

            $body = (string) $response->getBody();
            $result = $this->parseXmlValue($body, 'SendGroupSmsSimpleResult');

        } catch (\Exception $e) {
            return SmsResponse::failure('خطای ارتباط با سرور پارس گرین: ' . $e->getMessage());
        }

        if ($result !== null && (int) $result > 0) {
            return SmsResponse::success([
                'rec_id' => $result,
                'message' => 'پیام با موفقیت ارسال شد.',
            ]);
        }

        return SmsResponse::failure(
            $this->getErrorMessage((int) $result),
            $result,
            ['code' => $result]
        );
    }

    public function sendPattern(string|array $to, string $patternCode, array $variables): SmsResponse
    {
        $phones = $this->normalizePhones($to);
        $mobile = $phones[0];
        $signature = $this->config['signature'] ?? '';

        $otpCode = $variables['code'] ?? $variables['otp'] ?? $patternCode;

        $lang = $this->config['lang'] ?? 'fa';
        $otpType = (int) ($this->config['otp_type'] ?? 2);
        $patternId = (int) ($this->config['pattern_id'] ?? 1);

        try {
            $xml = $this->buildSendOtpXml($signature, $mobile, $otpCode, $lang, $otpType, $patternId);

            $response = $this->client->post($this->sendEndpoint, [
                'headers' => array_merge($this->defaultHeaders(), [
                    'SOAPAction' => 'http://tempuri.org/SendOtp',
                ]),
                'body' => $xml,
            ]);

            $body = (string) $response->getBody();
            $result = $this->parseXmlValue($body, 'SendOtpResult');

        } catch (\Exception $e) {
            return SmsResponse::failure('خطای ارتباط با سرور پارس گرین: ' . $e->getMessage());
        }

        if ($result !== null && (int) $result > 0) {
            $returnedOtp = $this->parseXmlValue($body, 'otpCode');

            return SmsResponse::success([
                'rec_id' => $result,
                'otp_code' => $returnedOtp ?? $otpCode,
                'message' => 'کد تأیید با موفقیت ارسال شد.',
            ]);
        }

        return SmsResponse::failure(
            $this->getErrorMessage((int) $result),
            $result,
            ['code' => $result]
        );
    }

    public function getCredit(): ?SmsResponse
    {
        $signature = $this->config['signature'] ?? '';

        try {
            $xml = $this->buildGetCreditXml($signature);

            $response = $this->client->post($this->profileEndpoint, [
                'headers' => array_merge($this->defaultHeaders(), [
                    'SOAPAction' => 'http://tempuri.org/GetCredit',
                ]),
                'body' => $xml,
            ]);

            $body = (string) $response->getBody();
            $credit = $this->parseXmlValue($body, 'GetCreditResult');

        } catch (\Exception $e) {
            return SmsResponse::failure('خطا در دریافت موجودی: ' . $e->getMessage());
        }

        if ($credit !== null && (int) $credit >= 0) {
            return SmsResponse::success([
                'credit' => $credit,
                'message' => "موجودی: {$credit} ریال",
            ]);
        }

        return SmsResponse::failure(
            $this->getErrorMessage((int) $credit),
            $credit
        );
    }

    public function getDelivery(string $recId): ?SmsResponse
    {
        $signature = $this->config['signature'] ?? '';

        try {
            $xml = $this->buildGetDeliveryXml($signature, $recId);

            $response = $this->client->post($this->sendEndpoint, [
                'headers' => array_merge($this->defaultHeaders(), [
                    'SOAPAction' => 'http://tempuri.org/GetDelivery',
                ]),
                'body' => $xml,
            ]);

            $body = (string) $response->getBody();
            $result = $this->parseXmlValue($body, 'GetDeliveryResult');

        } catch (\Exception $e) {
            return SmsResponse::failure('خطا در دریافت وضعیت: ' . $e->getMessage());
        }

        return SmsResponse::success([
            'delivery_status' => $result,
            'rec_id' => $recId,
        ]);
    }

    private function buildSendGroupSmsXml(string $signature, array $phones, string $message, string $sender): string
    {
        $toXml = '';
        foreach ($phones as $phone) {
            $toXml .= "<string>{$this->xmlEscape($phone)}</string>";
        }

        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
               xmlns:xsd="http://www.w3.org/2001/XMLSchema"
               xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <SendGroupSmsSimple xmlns="http://tempuri.org/">
      <signature>{$this->xmlEscape($signature)}</signature>
      <from>{$this->xmlEscape($sender)}</from>
      <to>
        {$toXml}
      </to>
      <text>{$this->xmlEscape($message)}</text>
      <isFlash>false</isFlash>
      <udh></udh>
    </SendGroupSmsSimple>
  </soap:Body>
</soap:Envelope>
XML;
    }

    private function buildSendOtpXml(
        string $signature,
        string $mobile,
        string $otpCode,
        string $lang,
        int $otpType,
        int $patternId
    ): string {
        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
               xmlns:xsd="http://www.w3.org/2001/XMLSchema"
               xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <SendOtp xmlns="http://tempuri.org/">
      <signature>{$this->xmlEscape($signature)}</signature>
      <mobile>{$this->xmlEscape($mobile)}</mobile>
      <Lang>{$this->xmlEscape($lang)}</Lang>
      <otpType>{$otpType}</otpType>
      <patternId>{$patternId}</patternId>
      <otpCode>{$this->xmlEscape($otpCode)}</otpCode>
    </SendOtp>
  </soap:Body>
</soap:Envelope>
XML;
    }

    private function buildGetCreditXml(string $signature): string
    {
        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
               xmlns:xsd="http://www.w3.org/2001/XMLSchema"
               xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <GetCredit xmlns="http://tempuri.org/">
      <signature>{$this->xmlEscape($signature)}</signature>
    </GetCredit>
  </soap:Body>
</soap:Envelope>
XML;
    }

    private function buildGetDeliveryXml(string $signature, string $recId): string
    {
        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
               xmlns:xsd="http://www.w3.org/2001/XMLSchema"
               xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <GetDelivery xmlns="http://tempuri.org/">
      <signature>{$this->xmlEscape($signature)}</signature>
      <recId>{$this->xmlEscape($recId)}</recId>
    </GetDelivery>
  </soap:Body>
</soap:Envelope>
XML;
    }


    private function parseXmlValue(string $xml, string $tagName): ?string
    {
        $simpleXml = simplexml_load_string($xml);

        if ($simpleXml === false) {
            return null;
        }

        $simpleXml->registerXPathNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');
        $simpleXml->registerXPathNamespace('ns', 'http://tempuri.org/');

        $result = $simpleXml->xpath("//ns:{$tagName}");

        if (!empty($result)) {
            return (string) $result[0];
        }

        return null;
    }

    /**
     * Escape کاراکترهای XML
     */
    private function xmlEscape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /**
     * پیام خطا بر اساس کد
     */
    private function getErrorMessage(int $code): string
    {
        $messages = [
            -1 => 'امضای دیجیتال معتبر نیست.',
            -2 => 'خطای داخلی سرور.',
            -3 => 'متن پیام خالی است.',
            -4 => 'شماره گیرنده معتبر نیست.',
            -5 => 'شماره ارسال معتبر نیست.',
            -6 => 'اعتبار کافی نیست.',
            -7 => 'محدودیت ارسال روزانه',
            -8 => 'محدودیت ارسال گروهی',
            -9 => 'خطای احراز هویت',
            -10 => 'آی‌پی غیرمجاز',
        ];

        return $messages[$code] ?? "خطای ناشناخته (کد: {$code})";
    }
}