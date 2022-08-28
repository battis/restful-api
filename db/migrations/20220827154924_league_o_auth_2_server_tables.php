<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class LeagueOAuth2ServerTables extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $users = $this->table("users", [
            "id" => false,
            "primary_key" => "username",
        ]);
        $users
            ->addColumn("username", "string")
            ->addColumn("password", "string")
            ->addColumn("display_name", "string")
            ->addColumn("scopes", "text", ["default" => json_encode([])])
            ->addTimestamps()
            ->create();

        $clients = $this->table("oauth2_clients", [
            "id" => false,
            "primary_key" => "client_id",
        ]);
        $clients
            ->addColumn("client_id", "string")
            ->addColumn("client_secret", "string")
            ->addColumn("display_name", "string")
            ->addColumn("redirect_uri", "string", ["null" => true])
            ->addColumn("user_id", "string", ["null" => true])
            ->addColumn("grant_types", "string", ["null" => true])
            ->addColumn("confidential", "boolean", ["default" => false])
            ->addTimestamps()
            ->create();

        $scopes = $this->table("scopes", [
            "id" => false,
            "primary_key" => "scope",
        ]);
        $scopes
            ->addColumn("scope", "string")
            ->addColumn("description", "text")
            ->addTimestamps()
            ->create();

        $authCodes = $this->table("oauth2_auth_codes", [
            "id" => false,
            "primary_key" => "auth_code",
        ]);
        $authCodes
            ->addColumn("auth_code", "string")
            ->addColumn("expiry", "datetime")
            ->addColumn("user_id", "string", ["null" => true])
            ->addColumn("scopes", "text", ["default" => json_encode([])])
            ->addColumn("client_id", "string")
            ->create();

        $accessTokens = $this->table("oauth2_access_tokens", [
            "id" => false,
            "primary_key" => "access_token",
        ]);
        $accessTokens
            ->addColumn("access_token", "string")
            ->addColumn("expiry", "datetime")
            ->addColumn("user_id", "string", ["null" => true])
            ->addColumn("scopes", "text", ["default" => json_encode([])])
            ->addColumn("client_id", "string")
            ->create();

        $refreshTokens = $this->table("oauth2_refresh_tokens", [
            "id" => false,
            "primary_key" => "refresh_token",
        ]);
        $refreshTokens
            ->addColumn("refresh_token", "string")
            ->addColumn("expiry", "datetime")
            ->addColumn("access_token_id", "string")
            ->create();
    }
}
