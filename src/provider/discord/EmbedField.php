<?php

namespace Pingumask\Discord;

class EmbedField
{
    public function __construct(
        public string $name,
        public string $value,
        public bool $inline = false,
    ) {
        if (strlen($this->value) > 1024) {
            $this->value = substr($this->value, 0, 1021) . "...";
        }
    }

    public static function __cast(\stdClass $source): self
    {
        return new self(
            name: $source?->name ?? '',
            value: $source?->value ?? '',
            inline: $source?->inline ?? false,
        );
    }
}
