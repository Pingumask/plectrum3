<?php

namespace Pingumask\Plectrum\Model;

interface CommandInterface
{
    public const NAME = "Missing Name";
    public const CATEGORY = "Missing Category";
    public const DESCRIPTION = "Missing Description";
    public const UTILISATION = "Missing Utilisation";
    public const OPTIONS = [];

    public static function getDefinition(): string;
    public static function execute($payload);
}
