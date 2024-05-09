<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * Атрибуты, которые могут быть присвоены массово.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'department_id',
        'last_project_id',
    ];

    /**
     * Атрибуты, которые должны быть скрыты для сериализации.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Атрибуты, которые должны быть отброшены.
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

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function last_project()
    {
        return $this->hasOne(Project::class, 'id', 'last_project_id');
    }


    public function projects_author()
    {
        return $this->hasMany(Project::class, 'author_id');
    }


    public function projects_manager()
    {
        return $this->hasMany(Project::class, 'manager_id');
    }

    public function projects_customer()
    {
        return $this->hasMany(Project::class, 'customer_id');
    }
}
