<?php


namespace Drystack\Admin\Traits;


use Drystack\Admin\Models\Ability;
use Drystack\Admin\Models\Role;
use Illuminate\Support\Facades\Cache;

trait WithPermissions {

    public function roles() {
        return $this->hasMany(Role::class);
    }

    public function abilities() {
        return $this->hasMany(Ability::class);
    }

    public function scopeHasAbility($query, string $ability, $model) {
        $entity = $this->getEntity($model);
        $id = is_object($model) ? $model->id : null;
        return $query->has('abilities', function ($q) use ($ability, $entity, $id) {
            $q->where('name', $ability)->where('entity', $entity);
            if (!is_null($id)) {
                $q->where('restricted_to', $id);
            } else {
                $q->whereNull('restricted_to');
            }
        })->orWhere->has('roles.abilities', function ($q) use ($ability, $entity, $id) {
            $q->where('name', $ability)->where('entity', $entity);
            if (!is_null($id)) {
                $q->where('restricted_to', $id);
            } else {
                $q->whereNull('restricted_to');
            }
        });
    }

    public function isAllowedTo(string $ability, $model) {
        $id = is_object($model) ? "_$model->id" : "";
        return Cache::rememberForever("prm_user_{$this->id}_$ability$id", function () use ($ability, $model) {
            return $this->hasAbility($ability, $model);
        });
    }

    public function addRole(string $role) {
        if ($this->has('roles', function ($q) use ($role) {
            $q->where('name', $role);
        })) return;
        $role = Role::where('name', $role)->first();
        $this->roles()->attach($role->id);
    }

    public function removeRole(string $role) {
        $role = Role::where('name', $role)->first();
        $this->roles()->detach($role->id);
    }

    public function syncRoles(array $roles) {
        $roles = Role::whereIn('name', $roles)->pluck('id')->toArray();
        $this->roles()->sync($roles);
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
        $this->abilities()->attach($ability->id);
    }

    public function removeAbilityTo(string $ability, $model) {
        $entity = $this->getEntity($model);
        $ability = Ability::where('name', $ability)->where('entity', $entity)->first();
        $this->abilities()->detach($ability->id);
    }

    public function syncAbilities(array $abilities, $model) {
        $entity = $this->getEntity($model);
        $abilities = Ability::whereIn('name', $abilities)->where('entity', $entity)->pluck('id')->toArray();
        $this->abilities()->sync($abilities);
    }

    private function getEntity($model) {
        return is_string($model) ? $model : get_class($model);
    }
}
