<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewCompany extends Model
{
    use HasFactory;

    protected $table = 'reviews_company'; // اسم الجدول
    protected $fillable = [
        'name',
        'email',
        'stars',
        'comment',
        'user_id',
        'type',
        'status',
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function reports()
    {
        return $this->hasMany(ReviewsCompanyReport::class, 'reviews_company_id');
    }
}
