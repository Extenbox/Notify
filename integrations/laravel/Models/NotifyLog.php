<?php

namespace Extenbox\Notify\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * مدل لاگ ارسال‌های پیامکی
 *
 * @property int         $id
 * @property string      $provider
 * @property string      $to
 * @property string      $type
 * @property string|null $message
 * @property string      $status
 * @property array|null  $response
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class NotifyLog extends Model
{
    protected $table = 'Notify_logs';

    protected $fillable = [
        'provider',
        'to',
        'type',
        'message',
        'status',
        'response',
    ];

    protected $casts = [
        'response' => 'array',
    ];

    // --- Scopes ---

    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    public function scopeProvider(Builder $query, string $provider): Builder
    {
        return $query->where('provider', $provider);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    // --- Helpers ---

    public function isSuccessful(): bool
    {
        return $this->status === 'sent';
    }

    /**
     * آمار کلی
     */
    public static function stats(): array
    {
        return [
            'total'   => static::count(),
            'sent'    => static::sent()->count(),
            'failed'  => static::failed()->count(),
            'today'   => static::today()->count(),
        ];
    }
}
