<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'email',
        'country',
        'phone',
        'url',
        'years_of_experience',
        'business_outline',
        'educational_background',
        'professional_affiliations',
        'strengths',
        'reasons_to_join',
        'referred_by',
        'accepted',
        'status',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
