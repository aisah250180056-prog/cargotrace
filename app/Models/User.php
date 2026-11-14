<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOperator(): bool
    {
        return $this->role === 'operator';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }
    /**
     * Relasi: 1 Customer memiliki banyak Shipment miliknya
     */
    public function shipments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Shipment::class, 'customer_id');
    }

    /**
     * Relasi: 1 User (Operator/Admin) bisa mencatat banyak tracking events
     */
    public function trackingEvents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TrackingEvent::class, 'created_by');
    }

    /**
     * Relasi: 1 User memiliki banyak notifikasi
     */
    public function notifications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
