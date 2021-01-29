<?php


namespace Drystack\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class Ability extends Model{
    public $timestamps = false;
    protected $table = 'prm_abilities';

    protected $guarded = [];
}
