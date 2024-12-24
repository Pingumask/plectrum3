<?php

namespace Pingumask\Discord;

use GuzzleHttp\Client;
use Pingumask\Discord\ChannelSelect;
use Pingumask\Discord\Embed;
use Pingumask\Discord\MessageData;
use Pingumask\Plectrum\App;
use Psr\Http\Message\ResponseInterface;
use stdClass;

class Message
{
    public MessageData $data;

    /**
     * @param list<Embed> $embeds
     * @param list<ComponentsRow> $components
     */
    public function __construct(
        public ?InteractionCallback $type = InteractionCallback::CHANNEL_MESSAGE_WITH_SOURCE,
        ?string $content = null,
        array $embeds = [],
        array $components = [],
        int $flags = 0,
    ) {
        $this->data = new MessageData(
            content: $content,
            embeds: $embeds,
            components: $components,
            flags: $flags,
        );
    }

    public static function __cast(stdClass $source): self
    {
        foreach ($source->embeds as &$embed) {
            $embed = Embed::__cast($embed);
        }
        unset($embed);
        foreach ($source->components as &$row) {
            $row = ComponentsRow::__cast($row);
        }
        unset($row);
        return new self(
            content: $source->content ?? null,
            embeds: $source->embeds ?? [],
            components: $source->components ?? [],
            flags: $source->flags ?? 0,
        );
    }

    public function addComponentsRow(): self
    {
        $this->data->components[] = new ComponentsRow();
        return $this;
    }

    public function addComponent(ComponentInterface $component): self
    {
        $this->data->components[array_key_last($this->data->components)]->addComponent($component);
        return $this;
    }

    public function addButton(
        ButtonStyle $style = ButtonStyle::DANGER,
        ?string $label = null,
        string $custom_id = 'GenericButton'
    ): self
    {
        $button = new Button(style: $style, label: $label, custom_id: $custom_id);
        $this->addComponent($button);
        return $this;
    }

    /**
     * @param ChannelType[] $channel_types
     */
    public function addChannelSelect(
        string $custom_id = 'GenericChannelSelect',
        string $placeholder = 'Select Channel',
        array $channel_types = [ChannelType::GUILD_TEXT]
    ): self
    {
        $select = new ChannelSelect(custom_id: $custom_id, placeholder: $placeholder, channel_types: $channel_types);
        $this->addComponent($select);
        return $this;
    }

    public function sendToChannel(int $channel): ResponseInterface
    {
        $body = json_encode($this->data);
        assert(is_string($body));

        $database = App::getDB();
        $sql = <<<SQL
            INSERT INTO plectrum_logs (`type`, headers, body) VALUES (?, ?, ?)
        SQL;
        $stmt = $database->prepare($sql);
        $stmt->execute(['post', "https://discord.com/api/v10/channels/{$channel}/messages", $body]);
        $result = self::makeApiCall(
            method: "POST",
            url: "https://discord.com/api/v10/channels/{$channel}/messages",
            body: $body,
        );
        $stmt->execute(['response', json_encode($result->getHeaders()), json_encode((string)$result->getBody())]);
        return $result;
    }

    public function updateMessage(int $channelId, int $messageId): ResponseInterface
    {
        $body = json_encode($this->data);
        assert(is_string($body));

        $database = App::getDB();
        $sql = <<<SQL
            INSERT INTO plectrum_logs (`type`, headers, body) VALUES (?, ?, ?)
        SQL;
        $stmt = $database->prepare($sql);
        $stmt->execute(['patch', "https://discord.com/api/v10/channels/{$channelId}/messages/{$messageId}", $body]);
        $result = self::makeApiCall(
            method: "PATCH",
            url: "https://discord.com/api/v10/channels/{$channelId}/messages/{$messageId}",
            body: $body,
        );
        $stmt->execute(['response', json_encode($result->getHeaders()), $body]);
        return $result;
    }

    /**
     * TODO: log call and response
     */
    private static function makeApiCall(string $url, string $body = '', string $method = 'GET'): ResponseInterface
    {
        $token = App::getConf('discord', 'token');
        $guzzleClient = new Client();
        return $guzzleClient->request(
            $method,
            $url,
            [
                'headers' => [
                    'Authorization' => "Bot $token",
                    'Content-Type' => 'application/json',
                ],
                'body' => $body,
            ]
        );
    }
}
