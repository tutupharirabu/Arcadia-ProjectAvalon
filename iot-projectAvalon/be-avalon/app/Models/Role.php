<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $primaryKey = 'roles_id';
    protected $table = 'roles';

    protected $fillable = [
        'title',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
