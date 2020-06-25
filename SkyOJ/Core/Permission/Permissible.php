<?php namespace SkyOJ\Core\Permission;

use SkyOJ\Core\User\User;

/**
 * Interface on basic permissions
 */
interface Permissible
{
    public function readable(User $user):bool;
    public function writeable(User $user):bool;
    public static function creatable(User $user):bool;
}