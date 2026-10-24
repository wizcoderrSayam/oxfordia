<?php

namespace App\Models;

use App\Enums\Role;
use App\Notifications\QueuedVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
            'role' => Role::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * The student record linked to this login, if any.
     *
     * @return HasOne<Student, $this>
     */
    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    public function isStaff(): bool
    {
        return $this->role === Role::Staff;
    }

    public function isStudent(): bool
    {
        return $this->role === Role::Student;
    }

    /**
     * Whether the user may access the /admin back office.
     */
    public function canAccessBackOffice(): bool
    {
        return $this->is_active && in_array($this->role, [Role::Admin, Role::Staff], true);
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new QueuedVerifyEmail);
    }

    /**
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        // Not queued: the payload would store a live reset token in the jobs table.
        $this->notify(new ResetPassword($token));
    }

    /**
     * Where the user should land after authenticating.
     */
    public function homeRoute(): string
    {
        return $this->isStudent() ? route('portal.dashboard') : route('admin.dashboard');
    }
}
