<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Handbook extends Model
{
    protected $fillable = ['year', 'grade', 'semester', 'lesson', 'content'];
}