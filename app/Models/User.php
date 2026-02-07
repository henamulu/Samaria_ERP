<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    protected $table = 'sam_user';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'department',
        'branch',
        'type',
        'firstname',
        'middlename',
        'lastname',
        'company_name',
        'user_name',
        'user_email',
        'phone_no',
        'user_password',
        'user_status',
        'role',
        'filename',
        'size',
        'i_type',
        'status',
        'user_for',
        'o_status',
        'ip_address',
        'user_type',
        'registered_by',
        'date_registered',
        'reset_type',
        'temp_key',
        'profile_image',
        'dashboard',
        'site',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'user_password',
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
            'date_registered' => 'date',
            'user_password' => 'hashed',
        ];
    }

    /**
     * Get the password attribute for authentication
     */
    public function getAuthPassword()
    {
        return $this->user_password;
    }

    /**
     * Get the email attribute for authentication
     */
    public function getEmailForPasswordReset()
    {
        return $this->user_email;
    }

    /**
     * Get the name attribute
     */
    public function getNameAttribute()
    {
        return trim($this->firstname . ' ' . $this->middlename . ' ' . $this->lastname);
    }
}
