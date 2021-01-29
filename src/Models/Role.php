<?php


namespace Drystack\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model{
    public $timestamps = false;
    protected $table = 'prm_roles';

    protected $guarded = [];

    public function abilities() {
        return $this->hasManyThrough(Ability::class, RoleAbility::class, 'ability_id', 'id');
    }

    public function rolesAbilities() {
        return $this->hasMany(RoleAbility::class);
    }
}
