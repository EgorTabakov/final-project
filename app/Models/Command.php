<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Http\Resources\HasJsonDates;

class Command extends Model
{
    use HasFactory, HasJsonDates;
    protected $fillable = [
        'command',
        'json',
        'project_id',
    ];
    public function project()
    {
      return $this->belongsTo(Project::class);
    }
}
