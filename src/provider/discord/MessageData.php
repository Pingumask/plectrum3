<?php

namespace Pingumask\Discord;

use Pingumask\Discord\Embed;

class MessageData
{
    /**
     * @param list<Embed> $embeds
     * @param list<ComponentsRow> $components
     */
    public function __construct(
        public ?string $content = null,
        public array $embeds = [],
        public array $components = [],
        public int $flags = 0,
    ) {
    }
}
