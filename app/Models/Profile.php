<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    protected $fillable = ['referral_code', 'phone', 'bio'];

    protected static function booted(): void
    {
        static::updating(function (Profile $profile) {
            if ($profile->isDirty(['sponsor_id', 'user_id', 'referral_code'])) {
                throw new \LogicException('Referral identity and sponsor cannot be changed.');
            }
        });
        static::creating(function (Profile $profile) {
            if ($profile->sponsor_id !== null && (int) $profile->sponsor_id === (int) $profile->user_id) {
                throw new \LogicException('A member cannot sponsor themselves.');
            }
        });
    }

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sponsor_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
