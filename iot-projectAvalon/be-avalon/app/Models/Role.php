<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{

    use HasFactory, HasUuids;
    protected $primaryKey = 'roles_id';
    protected $table = 'roles';

    protected $fillable = [
        'title',
    ];

    public function list_user()
    {
        return $this->hasMany(User::class);
    }
}
