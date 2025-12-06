<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'home_id',
        'field_name',
        'language',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'metadata',
        'order',
    ];

    protected $casts = [
        'metadata' => 'array',
        'file_size' => 'integer',
        'order' => 'integer',
    ];

    public function home()
    {
        return $this->belongsTo(Home::class);
    }
}

