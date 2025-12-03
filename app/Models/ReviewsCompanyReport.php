<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewsCompanyReport extends Model
{
    use HasFactory;
    protected $fillable = [
        'reviews_company_id',
        'comment',
        'file',
        'status',
    ];

    public function reviewCompany()
    {
        return $this->belongsTo(ReviewCompany::class, 'reviews_company_id');
    }
}
