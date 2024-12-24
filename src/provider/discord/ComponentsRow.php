<?php

namespace Pingumask\Discord;

class ComponentsRow
{
    public ComponentType $type = ComponentType::ACTION_ROW;

    /**
     * @param list<ComponentInterface> $components
     */
    public function __construct(
        public array $components = [],
    ) {
    }

    public function addComponent(ComponentInterface $component): void
    {
        $this->components[] = $component;
    }

    public static function __cast(\stdClass $source): self
    {
        if (property_exists($source, 'component') && is_iterable($source->component)) {
            foreach ($source->component as &$component) {
                if ($component?->type == ComponentType::BUTTON->value) {
                    $component = Button::__cast($component);
                }
            }
            unset($component);
        }
        return new self(
            components: $source?->components ?? [],
        );
    }
}
