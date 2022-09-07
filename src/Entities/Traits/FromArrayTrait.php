<?php

namespace Battis\OAuth2\Server\Entities\Traits;

trait FromArrayTrait
{
    public static function fromArray(array $data = []): self
    {
        foreach (get_object_vars($obj = new self()) as $property => $default) {
            $obj->$property = $data[$property] ?? $default;
        }
        return $obj;
    }
}
