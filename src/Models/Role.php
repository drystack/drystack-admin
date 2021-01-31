<?php


namespace Drystack\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model{
    public $timestamps = false;
    protected $table = 'prm_roles';

    protected $guarded = [];

    public function abilities() {
        return $this->hasManyThrough(Ability::class, RoleAbility::class,
            'role_id', //foreign key on RoleAbility (1)
            'id', //foreign key on Ability (2)
            'id', //local key on Role (1)
            'ability_id'); //local key on RoleAbility (2)
    }

    public function rolesAbilities() {
        return $this->hasMany(RoleAbility::class);
    }
}
