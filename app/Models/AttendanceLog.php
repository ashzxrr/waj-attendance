<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    protected $table = 'attendance_logs';

    protected $fillable = [
        'pin',
        'datetime',
        'tanggal',
        'type',
        'photo_path',
        'latitude',
        'longitude',
        'distance_from_office',
        'is_within_geofence',
        'face_match_score',
        'face_verified',
        'device_info',
        'status',
        'synced_to_hris_at',
        'reviewed_by',
        'reviewed_at',
        'review_note',
    ];

    protected function casts(): array
    {
        return [
            'datetime' => 'datetime',
            'tanggal' => 'date',
            'latitude' => 'float',
            'longitude' => 'float',
            'distance_from_office' => 'float',
            'is_within_geofence' => 'boolean',
            'face_match_score' => 'float',
            'face_verified' => 'boolean',
            'synced_to_hris_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(EmployeeCache::class, 'pin', 'pin');
    }
}