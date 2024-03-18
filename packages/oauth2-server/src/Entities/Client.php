<?php

namespace Battis\OAuth2\Server\Entities;

use Battis\CRUD;
use Battis\OAuth2\Server\Entities\Interfaces\Scopeable;
use Battis\OAuth2\Server\Entities\Interfaces\TokenGrantable;
use Battis\OAuth2\Server\Entities\Interfaces\UserAssignable;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

class Client extends CRUD\Record implements
    ClientEntityInterface,
    UserAssignable,
    Scopeable,
    TokenGrantable
{
    use EntityTrait, ClientTrait;

    protected static function defineSpec(): CRUD\Spec
    {
        return new CRUD\Spec(self::class, "oauth2_clients", "identifier", [
            "identifier" => "client_id",
            "name" => "display_name",
            "userIdentifier" => "user_id",
            "redirectUri" => "redirect_uri",
            "isConfidential" => "confidential",
        ]);
    }

    /** @var ?string */
    protected $userIdentifier = null;

    /** @var ScopeEntityInterface[] */
    protected $scopes = [];

    /** @var string[] */
    protected $grant_types = [];

    /** @var string|null */
    protected $description;

    public function getUserIdentifier(): ?string
    {
        return $this->user_id;
    }

    public function setScopes($scopes)
    {
        if (is_array($scopes)) {
            foreach ($scopes as $scope) {
                $this->scopes[] = Scope::read($scope);
            }
        }
    }

    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function setGrantTypes(array $grantTypes)
    {
        $this->grant_types = $grantTypes;
    }

    public function getGrantTypes(): array
    {
        return $this->grant_types;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
}
