<?php

namespace Pingumask\Discord;

use Pingumask\Discord\ButtonStyle;
use Pingumask\Discord\ComponentType;

class Button implements ComponentInterface
{
    public ComponentType $type = ComponentType::BUTTON;

    public function __construct(
        public ButtonStyle $style = ButtonStyle::PRIMARY,
        public ?string $label = null,
        public ?string $custom_id = null,
        public ?string $url = null,
        public bool $disabled = false,
    ) {
    }

    public static function __cast(\stdClass $source): self
    {
        return new self(
            style: $source?->style ?? ButtonStyle::PRIMARY,
            label: $source?->label ?? null,
            custom_id: $source?->custom_id ?? 'Button',
            url: $source?->url ?? null,
            disabled: $source?->disabled ?? false,
        );
    }
}
