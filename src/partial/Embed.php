<?php

namespace Pingumask\Plectrum\Partial;

class Embed
{
    /** @var ?array<string, string> */
    public ?array $footer;

    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        ?string $footerText = null
    ) {
        if (!is_null($footerText)) {
            $this->footer['text'] = $footerText;
        }
    }
}
