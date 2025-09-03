<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'slug',
        'difficulty',
        'description',
        'github_path',
        'github_url',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('completed')
            ->withTimestamps();
    }
}
