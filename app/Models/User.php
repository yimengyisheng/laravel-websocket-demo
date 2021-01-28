<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable
{
    use HasRoles;
    protected $table='users';

    public function getRole()
    {
        $this->assignRole (['name'=>'admin']);
        $this->getAllPermissions ();
    }

}
