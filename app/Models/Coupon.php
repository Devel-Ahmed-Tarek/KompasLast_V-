<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Factories\HasFactory;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'discount',
        'expires_at',
        'status',
        'type', // 'percentage' or 'fixed'
        'start_date',
        'end_date',
        'usage_limit',
        'multi_used',
        'is_active',
        'type_id',
        'created_by',        // Assuming you have a user ID for the creator
        'updated_by',        // Assuming you have a user ID for the updater
        'deleted_at',        // For soft deletes
        'deleted_by',        // Assuming you have a user ID for the deleter
        'deleted_at_reason', // Reason for deletion
        'deleted_at_date',   // Date of deletion
    ];

    public function companyCoupons()
    {
        return $this->hasMany(CompanyCoupon::class);
    }

    public function isValid()
    {
        return $this->status === 'active' && (! $this->expires_at || $this->expires_at > now());
    }

    public function type()
    {
        return $this->belongsTo(Type::class, 'type_id');
    }
}
