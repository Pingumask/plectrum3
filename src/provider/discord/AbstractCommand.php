<?php

namespace Pingumask\Discord;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Pingumask\Discord\InteractionInterface;
use Pingumask\Discord\Embed;
use Pingumask\Discord\InteractionCallback;
use Pingumask\Discord\Message;
use Pingumask\Plectrum\App;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractCommand extends AbstractInteraction
{
    public const NAME = "Missing Name";
    public const CATEGORY = "Missing Category";
    public const DESCRIPTION = "Missing Description";
    public const OPTIONS = [];

    /**
     * @return array<mixed> the definition of the command for registration process
     */
    public static function getDefinition(): array
    {
        return [
            'name' => static::NAME,
            'type' => 1,
            'description' => static::DESCRIPTION,
            'category' => static::CATEGORY,
            'options' => static::OPTIONS
        ];
    }
}
