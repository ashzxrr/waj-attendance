<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeFaceProfile extends Model
{
    protected $table = 'employee_face_profiles';

    protected $fillable = [
        'pin',
        'face_embedding',
        'photo_reference_path',
        'registered_at',
    ];

    protected function casts(): array
    {
        return [
            'registered_at' => 'datetime',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(EmployeeCache::class, 'pin', 'pin');
    }
}