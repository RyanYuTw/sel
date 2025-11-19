<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Handbook extends Model
{
    protected $fillable = [
        'year',
        'grade',
        'semester',
        'lesson',
        'content',
        'status',
        'published_at',
    ];
    
    public $timestamps = false;
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->created_at = time();
            $model->updated_at = time();
        });
        
        static::updating(function ($model) {
            $model->updated_at = time();
        });
    }
}
