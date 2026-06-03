<?php

namespace Extenbox\Notify\Contracts;

class SmsResponse
{
    public function __construct(
        public readonly bool   $success,
        public readonly string $message = '',
        public readonly mixed  $data = null,
        public readonly ?int   $statusCode = null,
    ) {}

    public static function success(mixed $data = null, string $message = 'پیامک با موفقیت ارسال شد'): static
    {
        return new static(true, $message, $data);
    }

    public static function failure(string $message = 'خطا در ارسال پیامک', ?int $statusCode = null, mixed $data = null): static
    {
        return new static(false, $message, $data, $statusCode);
    }

    public function isSuccessful(): bool
    {
        return $this->success;
    }

    public function toArray(): array
    {
        return [
            'success'     => $this->success,
            'message'     => $this->message,
            'data'        => $this->data,
            'status_code' => $this->statusCode,
        ];
    }
}
