<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolesAbilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prm_roles_abilities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->foreign('role_id')->references('id')->on('prm_roles')->cascadeOnDelete();
            $table->unsignedBigInteger('ability_id');
            $table->foreign('ability_id')->references('id')->on('prm_abilities')->cascadeOnDelete();
            $table->unsignedBigInteger('restricted_id')->nullable();

            $table->unique(['role_id', 'ability_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles');
    }
}
