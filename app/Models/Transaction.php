<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'image',
        'package_id',
        'status',
        'type',
    ];

    protected $appends = ['image_url'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id', 'id')->withTrashed();
    }

    public function getImageUrlAttribute()
    {
        if ($this->image != null) {
            return Storage::disk('s3')->temporaryUrl(
                $this->image, now()->addHours(12)
            );
        }

        return null;
    }
}
