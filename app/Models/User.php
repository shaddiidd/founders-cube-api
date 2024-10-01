<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'full_name',
        'url',
        'industry',
        'bio',
        'email',
        'phone_number',
        'password',
        'verified',
        'special_member',
        'user_type',
        'profile_picture',
        'application_id',
        'referral_code',
        'country',
    ];

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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $appends = ['profile_pic'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function application()
    {
        return $this->hasOne(Application::class);
    }

    public function user_application()
    {
        return $this->belongsTo(Application::class, 'application_id');
    }

    public function getProfilePicAttribute()
    {
        if ($this->profile_picture != null) {
            $thumb_path = '';
            $arr = explode('.', $this->profile_picture);
            foreach ($arr as $key => $str) {
                if (strlen($thumb_path) == 0) {
                    $thumb_path .= $str;
                } else {
                    if ($key == (sizeof($arr) - 1)) {
                        $thumb_path .= '.thumb.' . $str;
                    } else {
                        $thumb_path .= '.' . $str;
                    }
                }
            }
            return [
                'original' => Storage::disk('s3')->temporaryUrl(
                    $this->profile_picture, now()->addHours(12)
                ),
                'thumb' => Storage::disk('s3')->temporaryUrl(
                    $thumb_path, now()->addHours(12)
                ),
            ];
        }

        return null;
    }
}
