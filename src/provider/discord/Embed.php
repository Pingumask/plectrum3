<?php

namespace Pingumask\Discord;

use Pingumask\Discord\EmbedField;

class Embed
{
    /** @var ?array<string, string> */
    public ?array $footer = null;

    /** @var ?array<string, scalar> */
    public ?array $image = null;

    /**
     * @param EmbedField[] $fields
     */
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public array $fields = [],
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

    public function addField(string $name, string $value, bool $inline = false): self
    {
        $this->fields[] = new EmbedField(name: $name, value: $value, inline: $inline);
        return $this;
    }

    public function getField(string $name): ?EmbedField
    {
        return $this->fields[array_search($name, array_column($this->fields, 'name'))] ?? null;
    }

    public function setField(string $name, mixed $value): void
    {
        $this->fields[array_search($name, array_column($this->fields, 'name'))]->value = $value;
    }

    public static function __cast(\stdClass $source): self
    {
        foreach ($source->fields as &$field) {
            $field = EmbedField::__cast($field);
        }
        unset($field);
        return new self(
            title: $source->title ?? null,
            description: $source->description ?? null,
            fields: $source->fields ?? [],
            footerText: $source->footer->text ?? null,
            imageUrl: $source->image->url ?? null,
        );
    }
}
