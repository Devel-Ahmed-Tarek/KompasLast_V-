<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitorLink extends Model
{
    use HasFactory;
    protected $fillable = [
        'visit_count',
        'url',
        'visitor_id',

    ];

    // Relationship with VisitorLink
    public function Visitors()
    {
        return $this->belongsTo(Visitor::class);
    }
}
