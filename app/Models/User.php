<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

/**
 * @phan-property Organisation $organisation
 * @phan-property EloquentCollection<LoginToken> $loginTokens
 * @phan-property EloquentCollection<UserPermission> $userPermissions
 */
class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable;
    use Authorizable;
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        "name",
        "email",
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
        "id",
        "organisation_id",
        "password_hash",
        "activation_token",
    ];

    /**
     * List of type conversions for attributes
     *
     * @var array
     * @phan-var array<string, string>
     */
    protected $casts = [
        "enabled" => "boolean",
        "activated" => "boolean",
    ];

    /**
     * Gets the relation to the organisation
     *
     * @return BelongsTo
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    /**
     * Gets the relation to the login tokens
     *
     * @return HasMany
     */
    public function loginTokens(): HasMany
    {
        return $this->hasMany(LoginToken::class);
    }

    /**
     * Gets the relation to the users permissions
     *
     * @return HasMany
     */
    public function userPermissions(): HasMany
    {
        return $this->hasMany(UserPermission::class);
    }

    /**
     * Gets a list of assigned permissions
     *
     * @return EloquentCollection
     * @phan-return EloquentCollection<Permission>
     */
    public function getPermissions(): EloquentCollection
    {
        return $this->userPermissions->map(
            fn (UserPermission $user_permission): Permission => $user_permission->permission
        );
    }

    /**
     * Gets a list of assigned permission names
     *
     * @return EloquentCollection
     * @phan-return Collection<string>
     */
    public function getPermissionNames(): Collection
    {
        return $this->getPermissions()->map(
            fn (Permission $permission): string => $permission->name
        );
    }

    /**
     * Checks if the user has a permission granted
     *
     * @param string $name The name of the permission
     *
     * @return boolean
     */
    public function hasPermission(string $name): bool
    {
        $this->load("userPermissions.permission");

        $user_permission_names = $this->userPermissions->map(
            fn (UserPermission $user_permission): string => $user_permission->permission->name
        );

        return $user_permission_names->contains($name);
    }

    /**
     * Gives a permission to the user
     *
     * @param string $name The name of the permission to grant
     *
     * @return void
     */
    public function grantPermission(string $name): void
    {
        // Just do nothing if the user already has the permission
        if ($this->hasPermission($name)) {
            return;
        }

        $permission = Permission::query()
            ->where("name", $name)
            ->firstOrFail();

        $user_permission = new UserPermission();
        $user_permission->user_id = $this->id;
        $user_permission->permission_id = $permission->id;
        $user_permission->save();
    }

    /**
     * Removes a permission from the user
     *
     * @param string $name The name of the permission to remove
     *
     * @return void
     */
    public function revokePermission(string $name): void
    {
        $permission = Permission::query()
            ->where("name", $name)
            ->first();

        UserPermission::query()
            ->where("user_id", $this->id)
            ->where("permission_id", $permission->id)
            ->delete();
    }
}
