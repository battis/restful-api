<?php


namespace Battis\RestfulAPI;


use Battis\PersistentObject\Parts\Condition;
use Battis\PersistentObject\PersistentObject;
use Battis\PersistentObject\PersistentObjectException;
use Battis\PersistentObject\PerUser\PerUserObject;
use PDO;

/**
 * @method static RestfulObject[] getInstances(Condition $condition = null, $ordering = null, PDO $pdo = null)
 * @method static RestfulObject|null getInstanceById($id, Condition $condition = null, PDO $pdo = null)
 * @method static RestfulObject createInstance(array $values, bool $strict = true, bool $overwrite = false, PDO $pdo = null)
 * @method static RestfulObject deleteInstance(string $id, Condition $condition = null, PDO $pdo = null)
 */
abstract class RestfulObject extends PerUserObject
{
    use RestfulObjectTrait;

    protected static $USER_BINDING = RestfulUser::class;
}
