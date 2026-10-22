<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class City extends Model
{
    use HasFactory, HasTranslations;

    public const TYPE_CITY = 'city';
    public const TYPE_MUNICIPALITY = 'municipality';
    public const TYPE_REGION = 'region';

    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_COMING_SOON = 2;

    protected $fillable = [
        'name',
        'country_id',
        'state_id',
        'place_type',
        'ags_code',
        'latitude',
        'longitude',
        'status',
    ];

    public $translatable = ['name'];

    protected $casts = [
        'latitude'  => 'float',
        'longitude' => 'float',
        'status'    => 'integer',
    ];

    protected $appends = ['status_label', 'disabled'];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function companies()
    {
        return $this->belongsToMany(User::class, 'company_cities')
            ->withPivot('radius_km')
            ->withTimestamps();
    }

    public function scopeSelectable($query)
    {
        return $query->whereIn('place_type', [self::TYPE_CITY, self::TYPE_MUNICIPALITY]);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeVisibleToCompanies($query)
    {
        return $query->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_COMING_SOON]);
    }

    public function isAvailable(): bool
    {
        return (int) $this->status === self::STATUS_ACTIVE;
    }

    public function isComingSoon(): bool
    {
        return (int) $this->status === self::STATUS_COMING_SOON;
    }

    public function statusLabel(): string
    {
        return $this->status_label;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ((int) $this->status) {
            self::STATUS_INACTIVE => 'inactive',
            self::STATUS_COMING_SOON => 'coming_soon',
            default => 'active',
        };
    }

    public function getDisabledAttribute(): bool
    {
        return $this->isComingSoon();
    }
}
