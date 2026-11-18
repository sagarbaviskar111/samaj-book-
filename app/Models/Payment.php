<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    public const PURPOSES = ['donation', 'event_fee', 'premium', 'other'];
    public const STATUSES = ['pending', 'paid', 'failed', 'refunded'];
    public const GATEWAYS = ['manual', 'razorpay', 'phonepe', 'stripe'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['meta' => 'array', 'paid_at' => 'datetime', 'amount_paise' => 'integer'];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }

    /** Rupee amount as ₹ with Indian digit grouping (12,34,567.50). */
    public static function inr(int|float|null $paise, string $symbol = '₹'): string
    {
        $paise = (int) round((float) $paise);
        $rupees = intdiv(abs($paise), 100);
        $frac = abs($paise) % 100;
        $s = (string) $rupees;
        if (strlen($s) > 3) {
            $head = substr($s, 0, -3);
            $s = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $head).','.substr($s, -3);
        }

        return ($paise < 0 ? '-' : '').$symbol.$s.($frac ? '.'.str_pad((string) $frac, 2, '0', STR_PAD_LEFT) : '');
    }

    public function amountFormatted(): string
    {
        return static::inr($this->amount_paise, $this->currency === 'INR' ? '₹' : $this->currency.' ');
    }
}
