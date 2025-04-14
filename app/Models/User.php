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
        'username',
        'phone_num',
        'bank_name',
        'bank_account',
        'role',
        'status',
        'ic_num',
        'email',
        'password',
        'user_photo',
        'user_code',
        'module_permissions',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
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

    
    public function address()
    {
        return $this->hasOne(Addresses::class);
    }

    public function employment()
    {
        return $this->hasOne(Employments::class);
    }

    public function salary()
    {
        return $this->hasOne(Salaries::class);
    }

    public function redemption()
    {
        return $this->hasOne(Redemptions::class);
    }

    public function loanApplications()
    {
        return $this->hasMany(LoanApplications::class);
    }
    
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
    
    /**
     * Check if user has permission for a specific module
     *
     * @param int $moduleId
     * @return bool
     */
    public function hasModulePermission($moduleId)
    {
        if (!$this->module_permissions) {
            return false;
        }
        
        $permissions = is_array($this->module_permissions) 
            ? $this->module_permissions 
            : json_decode($this->module_permissions);
            
        return in_array($moduleId, $permissions);
    }

    public function hasRole($role)
    {
        return $this->role === $role;
    }
}
