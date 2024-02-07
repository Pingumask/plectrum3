<?php

namespace Pingumask\Plectrum\Partial;

use Pingumask\Plectrum\Partial\CommandInterface;

abstract class AbstractCommand implements CommandInterface
{
    public static function getDefinition(): array
    {
        return [
            'name' => static::NAME,
            'category' => static::CATEGORY,
            'description' => static::DESCRIPTION,
            'utilisation' => static::UTILISATION,
            'options' => static::OPTIONS
        ];
    }
}
