<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigApp extends Model
{
    use HasFactory;
    protected $fillable = [
        'add_offer', 'offer_flow', 'add_company', 'on_contact', 'on_shop', 'on_auth_company',
        'accept_dynamic_offer', 'add_finance_order', 'file', 'file2', 'file3', 'name',
        'address', 'email', 'email2', 'website', 'phone', 'number', 'bank_name',
        'bank_number', 'bank_ip', 'qrcode', 'logo_dark',

    ];
}
