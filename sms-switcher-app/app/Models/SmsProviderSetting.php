<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsProviderSetting extends Model
{
    protected $table = 'sms_provider_settings';

    protected $fillable = [
        'driver',
        'label',
        'config',
        'is_active',
        'is_default',
        'is_fallback',
    ];

    protected $casts = [
        'config'      => 'array',
        'is_active'   => 'boolean',
        'is_default'  => 'boolean',
        'is_fallback' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}