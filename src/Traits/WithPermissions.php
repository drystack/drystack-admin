<?php


namespace Drystack\Admin\Traits;


use Drystack\Admin\Models\Ability;
use Drystack\Admin\Models\Role;
use Drystack\Admin\Models\UserAbility;
use Drystack\Admin\Models\UserRole;

trait WithPermissions {

    public function roles() {
        return $this->hasManyThrough(Role::class, UserRole::class,
            'user_id',
            'id',
            'id',
            'role_id'
        );
    }

    public function abilities() {
        return $this->hasManyThrough(Ability::class, UserAbility::class,
            'user_id',
            'id',
            'id',
            'ability_id'
        );
    }

    public function usersRoles() {
        return $this->hasMany(UserRole::class);
    }

    public function usersAbilities() {
        return $this->hasMany(UserAbility::class);
    }

    public function scopeHasAbility($query, string $ability, $model) {
        $entity = $this->getEntity($model);
        $id = is_object($model) ? $model->id : null;

        return $query->whereHas('abilities', function ($q) use ($ability, $entity, $id) {
            $q->where('name', $ability)->where('entity', $entity);
//            if (!is_null($id)) {
//                $q->where('restricted_to', $id);
//            } else {
//                $q->whereNull('restricted_to');
//            }
        })->orWhereHas('roles.abilities', function ($q) use ($ability, $entity, $id) {
            $q->where('name', $ability)->where('entity', $entity);
        })->exists();
    }

    public function isAllowedTo(string $ability, $model) {
        return $this->hasAbility($ability, $model);
//        TODO cache abilities
//        $id = is_object($model) ? "_$model->id" : "";
//        return Cache::get("prm_user_{$this->id}_$ability$id", function () use ($ability, $model) {
//            return $this->hasAbility($ability, $model);
//        });
    }

    public function addRole(string $role) {
        if ($this->whereHas('roles', function ($q) use ($role) {
            $q->where('name', $role);
        })->exists()) return;
        $role = Role::where('name', $role)->first();
        $this->usersRoles()->insert([
            'role_id' => $role->id,
            'user_id' => $this->id
        ]);
    }

    public function removeRole(string $role) {
        $role = Role::where('name', $role)->first();
        $this->usersRoles()
             ->where('user_id', $this->id)
             ->where('role_id', $role->id)
             ->delete();
    }

    public function syncRoles(array $roles) {
        $roles = Role::whereIn('name', $roles)->pluck('id')->toArray();
        $this->usersRoles()->delete();
        $insert = [];
        foreach ($roles as $role_id) {
            $insert[] = [
                'role_id' => $role_id,
                'user_id' => $this->id
            ];
        }
        $this->usersRoles()->insert($insert);
    }

    public function addAbilityTo(string $ability, $model) {
        $entity = $this->getEntity($model);
        if ($this->has('abilities', function ($q) use ($ability, $entity) {
            $q->where('name', $ability)->where('entity', $entity);
        })) return;
        $ability = Ability::firstOrCreate([
            'name' => $ability,
            'entity' => $entity
        ]);
        $this->usersAbilities()->insert([
            'ability_id' => $ability->id,
            'user_id' => $this->id
        ]);
    }

    public function removeAbilityTo(string $ability, $model) {
        $entity = $this->getEntity($model);
        $ability = Ability::where('name', $ability)->where('entity', $entity)->first();
        $this->usersAbilities()->where('ability_id', $ability->id)->delete();
    }

    public function syncAbilities(array $abilities, $model) {
        $entity = $this->getEntity($model);
        $abilities = Ability::whereIn('name', $abilities)->where('entity', $entity)->pluck('id')->toArray();
        $this->usersAbilities()->delete();
        $insert = [];
        foreach ($abilities as $ability_id) {
            $insert[] = [
                'ability_id' => $ability_id,
                'user_id' => $this->id
            ];
        }
        $this->usersAbilities()->insert($insert);
    }

    private function getEntity($model) {
        return is_string($model) ? $model : get_class($model);
    }
}
