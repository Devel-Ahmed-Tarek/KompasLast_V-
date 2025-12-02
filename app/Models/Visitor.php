<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    use HasFactory;
    protected $fillable = [
        'ip_address',
        'country',
        'city',
        'device',
        'browser',
    ];

    // Relationship with VisitorLink
    public function links()
    {
        return $this->hasMany(VisitorLink::class);
    }
}
