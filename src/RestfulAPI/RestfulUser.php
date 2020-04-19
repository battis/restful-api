<?php


namespace Battis\RestfulAPI;


use Battis\PersistentObject\Parts\Condition;
use Battis\PersistentObject\PerUser\User;

/**
 * @method static RestfulUser[] getInstances(Condition $condition = null, $ordering = null, PDO $pdo = null)
 * @method static RestfulUser|null getInstanceById($id, Condition $condition = null, PDO $pdo = null)
 * @method static RestfulUser createInstance(array $values, bool $strict = true, bool $overwrite = false, PDO $pdo = null)
 * @method static RestfulUser deleteInstance(string $id, Condition $condition = null, PDO $pdo = null)
 */
class RestfulUser extends User
{
    use RestfulObjectTrait;
}
