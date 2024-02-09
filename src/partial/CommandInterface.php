<?php

namespace Pingumask\Plectrum\Partial;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

interface CommandInterface
{
    public const NAME = "Missing Name";
    public const CATEGORY = "Missing Category";
    public const DESCRIPTION = "Missing Description";
    public const OPTIONS = [];

    /** @return array<mixed> */
    public static function getDefinition(): array;
    public static function execute(Request $request): Response;
}
