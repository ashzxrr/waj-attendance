<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class EmployeeAuth extends Model
{
    use HasApiTokens;

    protected $table = 'employee_auth';

    protected $fillable = [
        'pin',
        'pin_absensi',
        'kode_absensi_is_default',
        'device_id',
        'last_login_at',
    ];

    protected $hidden = [
        'pin_absensi',
    ];

    protected function casts(): array
    {
        return [
            'kode_absensi_is_default' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function setPinAbsensiAttribute($value)
    {
        $this->attributes['pin_absensi'] = bcrypt($value);
    }

    public function employee()
    {
        return $this->belongsTo(EmployeeCache::class, 'pin', 'pin');
    }
}