<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $fillable = [
        'hash',
        'path',
        'filename',
        'mime_type',
        'size',
        'width',
        'height',
        'created_at',
        'updated_at',
    ];

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (!$model->created_at) {
                $model->created_at = time();
            }
            if (!$model->updated_at) {
                $model->updated_at = time();
            }
        });
        
        static::updating(function ($model) {
            $model->updated_at = time();
        });
    }

    protected $casts = [
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'created_at' => 'integer',
        'updated_at' => 'integer',
    ];
}
