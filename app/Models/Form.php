<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Form extends Model
{
    use HasFactory, HasTranslations;
    protected $guarded = [];

    protected $translatable = [
        'header',
        'sub_title',
        'step1_title',
        'step2_title',
        'service',
        'name_last',
        'name_first',
        'email',
        'phone_number',
        'current_location',
        'current_city',
        'current_rooms_number',
        'current_floor',
        'current_elevator',
        'current_country',
        'current_zipcode',
        'new_location',
        'new_city',
        'new_rooms_number',
        'new_floor',
        'new_elevator',
        'new_country',
        'new_zipcode',
        'date',
        'offers_number',
        'other_details',
        'note',
        'next_button',
        'submit_button',
        'success_message',
        'success_message_title',
    ];
}
