<?php

namespace Pingumask\Discord;

use Pingumask\Discord\ComponentType;

class ChannelSelect implements ComponentInterface
{
    public ComponentType $type = ComponentType::CHANNEL_SELECT;

    /**
     * @param ?ChannelType[] $channel_types
     * @param ?mixed[] $default_values
     * TODO: create DefaultValue class
     */
    public function __construct(
        public ?string $custom_id = null,
        public ?array $channel_types = [ChannelType::GUILD_TEXT],
        public string $placeholder = 'Select Channel',
        public ?array $default_values = [],
        public ?int $min_values = null,
        public ?int $max_values = null,
        public bool $disabled = false,
    ) {
    }

    public static function __cast(\stdClass $source): self
    {
        return new self(
            custom_id: $source->custom_id ?? null,
            channel_types: $source->channel_types ?? [ChannelType::GUILD_TEXT],
            placeholder: $source->placeholder = 'Select Channel',
            default_values: $source->default_values = [],
            min_values: $source->min_values = null,
            max_values: $source->max_values = null,
            disabled: $source->disabled = false,
        );
    }
}
