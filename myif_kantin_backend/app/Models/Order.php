<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $primaryKey = 'order_id';

    protected $fillable = [
        'user_id',
        'room_number',
        'building_name',
        'courier_name',
        'shipping_fee',
        'total_price',
        'status',
        'payment_status',
        'payment_method',
        'payment_proof',
        'delivery_time',
        'delivery_notes',
    ];

    protected $casts = [
        'shipping_fee' => 'decimal:2',
        'total_price' => 'decimal:2',
        'delivery_time' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function getFormattedDeliveryTimeAttribute()
    {
        return $this->delivery_time ? $this->delivery_time->format('d M Y, H:i') : null;
    }

    public function getDeliveryDateAttribute()
    {
        return $this->delivery_time ? $this->delivery_time->format('Y-m-d') : null;
    }

    public function getDeliveryTimeOnlyAttribute()
    {
        return $this->delivery_time ? $this->delivery_time->format('H:i') : null;
    }

    public function getPaymentProofUrlAttribute()
    {
        return $this->payment_proof ? asset('assets/payment_proof/' . $this->payment_proof) : null;
    }
}