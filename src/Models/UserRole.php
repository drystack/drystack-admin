<?php


namespace Drystack\Admin\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserRole extends Pivot {
    public $timestamps = false;
    protected $table = 'prm_users_roles';

    protected $guarded = [];
}
