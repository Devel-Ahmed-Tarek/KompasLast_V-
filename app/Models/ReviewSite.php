<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewSite extends Model
{
    use HasFactory;
    protected $table = 'reviews_site';

    protected $fillable = [
        'name',
        'email',
        'stars',
        'comment',
        'status',
    ];
}
