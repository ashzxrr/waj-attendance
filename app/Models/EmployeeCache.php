<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeCache extends Model
{
    protected $table = 'employees_cache';

    protected $fillable = [
        'pin',
        'nama',
        'nik',
        'is_active',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'synced_at' => 'datetime',
        ];
    }

    public function auth()
    {
        return $this->hasOne(EmployeeAuth::class, 'pin', 'pin');
    }
}