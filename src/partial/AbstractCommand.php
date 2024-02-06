<?php

namespace Pingumask\Plectrum\Partial;

use Pingumask\Plectrum\Partial\CommandInterface;

abstract class AbstractCommand implements CommandInterface
{
    public static function getDefinition(): string
    {
        return json_encode([
            "name" => self::NAME,
            "category" => self::CATEGORY,
            "description" => self::DESCRIPTION,
            "utilisation" => self::UTILISATION,
            "options" => self::OPTIONS
        ]) ?: '';
    }
}
