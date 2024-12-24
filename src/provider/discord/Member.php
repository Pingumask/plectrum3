<?php

namespace Pingumask\Discord;

use Psr\Http\Message\ResponseInterface;

class Member
{
    public static function setNickname(int $guild_id, int $user_id, ?string $nickname): ResponseInterface
    {
        $body = sprintf(<<<JSON
        {
            "nick": %s
        }
        JSON, $nickname ? '"' . $nickname . '"' : "null");
        return AbstractInteraction::makeApiCall(
            method: 'PATCH',
            url: "https://discord.com/api/v10/guilds/{$guild_id}/members/{$user_id}",
            body: $body,
        );
    }
}
