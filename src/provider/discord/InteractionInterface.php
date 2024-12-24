<?php

namespace Pingumask\Discord;

use GuzzleHttp\Psr7\Request;

interface InteractionInterface
{
    public static function execute(Request $request): void;
}
