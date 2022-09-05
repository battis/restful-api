<?php

namespace Battis\OAuth2\Server\Entities\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait SerializeScopesTrait
{
  public function scopes(): Attribute
  {
    return new Attribute(
      fn($value) => json_decode($value),
      fn($value) => json_encode($value)
    );
  }
}
