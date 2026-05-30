<?php

namespace Extenbox\Notify\Channels;

class NotifakMessage
{
    protected string  $content     = '';
    protected ?string $phone       = null;
    protected ?string $provider    = null;
    protected ?string $sender      = null;
    protected string  $type        = 'normal';
    protected ?string $patternCode = null;
    protected array   $variables   = [];

    public static function create(string $content = ''): static
    {
        return (new static)->content($content);
    }

    public function content(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function to(string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function via(string $provider, ?string $sender = null): static
    {
        $this->provider = $provider;
        $this->sender   = $sender;
        return $this;
    }

    public function sender(string $sender): static
    {
        $this->sender = $sender;
        return $this;
    }

    public function pattern(string $patternCode, array $variables = []): static
    {
        $this->type        = 'pattern';
        $this->patternCode = $patternCode;
        $this->variables   = $variables;
        return $this;
    }

    // --- Getters ---

    public function getContent(): string     { return $this->content; }
    public function getPhone(): ?string      { return $this->phone; }
    public function getProvider(): ?string   { return $this->provider; }
    public function getSender(): ?string     { return $this->sender; }
    public function getType(): string        { return $this->type; }
    public function getPatternCode(): ?string{ return $this->patternCode; }
    public function getVariables(): array    { return $this->variables; }
}
