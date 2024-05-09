<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Http\Resources\HasJsonDates;

class Project extends Model
{
    use HasFactory, HasJsonDates, SoftDeletes;

    protected $fillable = [
        'name',
        'author_id',
        'manager_id',
        'customer_id',
        'locked',
    ];

    public function commands()
    {
        return $this->hasMany(Command::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'id', 'author_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'id', 'manager_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'id', 'customer_id');
    }

}
