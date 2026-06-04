<?php

namespace Extenbox\Notify;

use Extenbox\Notify\Contracts\SmsResponse;

/**
 * PendingSms - بیلدر زنجیره‌ای برای ارسال پیامک
 *
     * Notify::message($phone, $msg)
     *   ->via('smsir', '3000...')
     *   ->type('pattern', 'template_code', ['key' => 'value'])
     *   ->dispatch()
 */
class PendingSms
{
    protected string|array $to;
    protected string       $message;
    protected ?string      $provider    = null;
    protected ?string      $sender      = null;
    protected string       $type        = 'normal';
    protected ?string      $patternCode = null;
    protected array        $variables   = [];
    protected NotifyManager $manager;

    public function __construct(NotifyManager $manager, string|array $to, string $message)
    {
        $this->manager = $manager;
        $this->to      = $to;
        $this->message = $message;
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
     * تعیین نوع ارسال
     *
     * ->type('normal')
     * ->type('pattern', 'template_code', ['name' => 'Ali', 'code' => '12345'])
     */
    public function type(string $type, ?string $patternCode = null, array $variables = []): static
    {
        $this->type        = $type;
        $this->patternCode = $patternCode;
        $this->variables   = $variables;
        return $this;
    }

    /**
     * ارسال فوری
     * اگر auto_send روشن باشد، __destruct همین متد را برای استفاده ساده README صدا می‌زند.
     */
    private bool $sent = false;

    public function send(): SmsResponse
    {
        return $this->dispatch();
    }

    public function dispatch(): SmsResponse
    {
        $this->sent = true;
        return $this->manager->dispatch($this);
    }

    public function deliver(): SmsResponse
    {
        return $this->dispatch();
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

    public function getType(): string
    {
        return $this->type;
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
