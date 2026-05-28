<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\OrderTask;

class Discipline extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'department_id'];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'discipline_id');
    }

    public function tasks()
    {
        return $this->hasMany(OrderTask::class, 'discipline_id');
    }
}
