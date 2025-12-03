<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferFakeReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'shopping_list_id',
        'status',
    ];

    public function shopping_list()
    {
        return $this->belongsTo(Shopping_list::class, 'shopping_list_id');
    }
}
