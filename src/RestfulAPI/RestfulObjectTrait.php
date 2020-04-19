<?php


namespace Battis\RestfulAPI;


use Battis\PersistentObject\Parts\Condition;
use Battis\PersistentObject\PersistentObject;
use Battis\PersistentObject\PersistentObjectException;

trait RestfulObjectTrait
{
    public static $ID_PATTERN = '[0-9]+';

    public function isValidId($id): bool
    {
        return preg_match('/' . self::$ID_PATTERN . '/', $id);
    }

    /**
     * @param $id
     * @param Condition|null $condition
     * @param PDO|null $pdo
     * @return PersistentObject|null
     * @throws PersistentObjectException
     */
    public static function getInstanceByIdIfExists($id, Condition $condition = null, PDO $pdo = null)
    {
        try {
            return static::getInstanceById($id, $condition, $pdo);
        } catch (PersistentObjectException $exception) {
            if ($exception->getCode() === PersistentObjectException::NO_SUCH_INSTANCE) {
                return null;
            }
            throw $exception;
        }
    }

    /**
     * @param string $id
     * @param Condition|null $condition
     * @param PDO|null $pdo
     * @return PersistentObject|null
     * @throws PersistentObjectException
     */
    public static function deleteInstanceIfExists(string $id, Condition $condition = null, PDO $pdo = null)
    {
        try {
            return static::deleteInstance($id, $condition, $pdo);
        } catch (PersistentObjectException $exception) {
            if ($exception->getCode() === PersistentObjectException::NO_SUCH_INSTANCE) {
                return null;
            }
            throw $exception;
        }
    }

    /**
     * @param $field
     * @param $value
     * @return mixed|void
     * @throws PersistentObjectException
     */
    public function setIfExists($field, $value)
    {
        try {
            return parent::set($field, $value);
        } catch (PersistentObjectException $exception) {
            if ($exception->getCode() === PersistentObjectException::MUTATOR_NOT_DEFINED) {
                return;
            }
            throw $exception;
        }
    }

}
