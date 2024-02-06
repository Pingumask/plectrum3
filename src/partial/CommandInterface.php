<?php

namespace Pingumask\Plectrum\Partial;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

interface CommandInterface
{
    public const NAME = "Missing Name";
    public const CATEGORY = "Missing Category";
    public const DESCRIPTION = "Missing Description";
    public const UTILISATION = "Missing Utilisation";
    public const OPTIONS = [];

    public static function getDefinition(): string;
    public static function execute(Request $request): Response;
}
