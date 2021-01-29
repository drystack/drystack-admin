<?php


namespace Drystack\Admin\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class RoleAbility extends Pivot {
    public $timestamps = false;
    protected $table = 'prm_roles_abilities';

    protected $guarded = [];
}
