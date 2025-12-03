<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'img',   // Add img to fillable
        'phone', // Add phone to fillable
    ];

    /**
     * Accessor for profile_image
     */
    public function getProfileImageAttribute()
    {
        return $this->img ? url('uploads/' . $this->img) : null;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    protected $casts = [
        'files_links' => 'array',
    ];

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }
    public function companyDetails()
    {
        return $this->hasOne(CompanyDetail::class);
    }
    public function review()
    {
        return $this->hasMany(ReviewCompany::class);
    }

    public function typesComapny()
    {
        return $this->belongsToMany(Type::class, 'type_user');
    }

    public function shopping_list()
    {
        return $this->hasMany(Shopping_list::class, 'user_id');
    }

    public function generateOtp()
    {
        $this->otp            = rand(100000, 999999);  // إنشاء OTP مكون من 6 أرقام
        $this->otp_expired_at = now()->addMinutes(10); // تحديد انتهاء صلاحية OTP بعد 10 دقائق
        $this->save();
    }

}
