<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceSearchLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['term'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }
}