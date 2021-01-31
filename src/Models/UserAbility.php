<?php


namespace Drystack\Admin\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserAbility extends Pivot {
    public $timestamps = false;
    protected $table = 'prm_users_abilities';

    protected $guarded = [];
}
