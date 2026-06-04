<?php

namespace Extenbox\Notify;

use Extenbox\Notify\Contracts\SmsResponse;

/**
 * PendingSms - بیلدر زنجیره‌ای برای ارسال پیامک
 *
 * Notify::sms($phone, $msg)->via('smsir', '3000...')->send()
 * Notify::flash($phone, 'template_code', ['key' => 'value'])->via('smsir')->send()
 */
class PendingSms
{
    protected string|array $to;
    protected string       $message;
    protected ?string      $provider    = null;
    protected ?string      $sender      = null;
    protected ?string      $patternCode = null;
    protected array        $variables   = [];
    protected NotifyManager $manager;

    public function __construct(
        NotifyManager $manager,
        string|array $to,
        string $message = '',
        ?string $patternCode = null,
        array $variables = []
    ) {
        $this->manager     = $manager;
        $this->to          = $to;
        $this->message     = $message;
        $this->patternCode = $patternCode;
        $this->variables   = $variables;
    }

    /**
     * تعیین پنل و شماره ارسال
     *
     * ->via('smsir')
     * ->via('smsir', '3000xxxx')
     */
    public function via(string $provider, ?string $sender = null): static
    {
        $this->provider = $provider;
        $this->sender   = $sender;
        return $this;
    }

    /**
     * ارسال فوری
     * اگر auto_send روشن باشد، __destruct همین متد را برای استفاده ساده README صدا می‌زند.
     */
    private bool $sent = false;

    public function send(): SmsResponse
    {
        $this->sent = true;
        return $this->manager->dispatch($this);
    }

    public function dispatch(): SmsResponse
    {
        return $this->send();
    }

    public function deliver(): SmsResponse
    {
        return $this->send();
    }

    public function __destruct()
    {
        if (!$this->sent && $this->manager->shouldAutoSend()) {
            $this->send();
        }
    }

    // --- Getters ---

    public function getTo(): string|array
    {
        return $this->to;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function getSender(): ?string
    {
        return $this->sender;
    }

    public function getPatternCode(): ?string
    {
        return $this->patternCode;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }
}
