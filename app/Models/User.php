<?php

namespace App\Models;

use App\Enums\User\UserRole;
use App\Enums\User\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'password', 'role', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** SĐT Việt Nam: 10 số bắt đầu bằng 0 — dùng chung cho mọi validator có field phone. */
    public const PHONE_REGEX = '/^0\\d{9}$/';

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

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return UserRole::isAdmin($this->role);
    }

    /**
     * @return bool
     */
    public function isOwner(): bool
    {
        return UserRole::isOwner($this->role);
    }

    /**
     * @return bool
     */
    public function isMember(): bool
    {
        return UserRole::isMember($this->role);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return UserStatus::isActive($this->status);
    }

    /**
     * Vai trò dạng enum (null nếu cột chứa giá trị lạ).
     *
     * @return UserRole|null
     */
    public function roleEnum(): ?UserRole
    {
        return UserRole::tryFrom((string) $this->role);
    }

    /**
     * Trạng thái dạng enum (null nếu cột chứa giá trị lạ).
     *
     * @return UserStatus|null
     */
    public function statusEnum(): ?UserStatus
    {
        return UserStatus::tryFrom((string) $this->status);
    }
}
