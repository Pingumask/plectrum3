<?php

namespace Pingumask\Plectrum\Model;

use Pingumask\Plectrum\Model\CommandInterface;

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
        ]);
    }
}
