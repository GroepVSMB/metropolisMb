<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
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
        'password',
        'role', // Added role to fillable attributes
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
            'role' => UserRole::class
        ];
    }

    public function hasRole(string|UserRole $checkRole): bool
    {
        // 1. Get the current user's role as a standardized string
        // If it's an Enum, grab ->value, otherwise use the string directly.
        $userRole = $this->role instanceof \BackedEnum ? $this->role->value : $this->role;

        // 2. ADMIN OVERRIDE:
        // If the user is an Admin, they pass every check automatically.
        if ($userRole === UserRole::ADMIN->value) {
            return true;
        }

        // 3. Standardize the role we are checking against
        // If the input is an Enum, grab ->value, otherwise use the string.
        $checkValue = $checkRole instanceof \BackedEnum ? $checkRole->value : $checkRole;

        // 4. Perform the comparison
        return $userRole === $checkValue;
    }

    public function getRoleLabel(): string
    {
        // 1. Get the role string (handle both Enum object and plain string)
        $role = $this->role instanceof \BackedEnum ? $this->role->value : $this->role;
        
        // 2. Return it capitalized (e.g., "manager" -> "Manager")
        return ucfirst($role);
    }
}
