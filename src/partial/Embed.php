<?php

namespace Pingumask\Plectrum\Partial;

class Embed
{
    /** @var ?array<string, string> */
    public ?array $footer;

    /** @var ?array<string, scalar> */
    public ?array $image;

    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        ?string $footerText = null,
        ?string $imageUrl = null
    ) {
        if (!is_null($footerText)) {
            $this->footer['text'] = $footerText;
        }
        if (!is_null($imageUrl)) {
            $this->image['url'] = $imageUrl;
        }
    }
}
