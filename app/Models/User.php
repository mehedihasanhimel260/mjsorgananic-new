<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'location_permission',
        'last_visit_at',
        'last_logged_at',
        'last_user_agent',

        // IP location
        'ip_address',
        'ip_division',
        'ip_district',
        'ip_thana',
        'ip_postcode',

        // GPS location
        'gps_lat',
        'gps_lng',
        'gps_address',

        // Saved address
        'saved_division',
        'saved_district',
        'saved_thana',
        'saved_postcode',
        'saved_address',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'gps_lat' => 'decimal:7',
            'gps_lng' => 'decimal:7',
            'last_visit_at' => 'datetime',
            'last_logged_at' => 'datetime',
        ];
    }
}
