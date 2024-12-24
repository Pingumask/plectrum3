<?php

namespace Pingumask\Plectrum\Command;

use DOMDocument;
use DOMXPath;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use Pingumask\Discord\AbstractCommand;
use Pingumask\Discord\ButtonStyle;
use Pingumask\Discord\Embed;
use Pingumask\Discord\EmbedField;
use Pingumask\Discord\Flag;
use Pingumask\Discord\InteractionCallback;
use Pingumask\Discord\Message;
use Pingumask\Discord\OptionType;

class Dico extends AbstractCommand
{
    public const NAME = 'dico';
    public const CATEGORY = 'fun';
    public const DESCRIPTION = "Cherche la définition d'un mot sur https://fr.wiktionary.org/";
    public const LINK_CONVERSION = '@<a.*href="(.+)".*>(.+)</a>@U';
    public const REPAIR_LINK_SPACE = '@\(https://([\w\.\d\%\/:-]+)(\s)([\s\w\.\d\%\/:-]+)\)@Uu';
    public const INVISIBLE_UNICODE = '@&#160;@U';
    public const ANNOTATION_LINK = '@<a href="([a-zA-Z\.:/-]+)"[ \w\.:/"=-]+>&#91;\d+&#93;</a>@U';
    public const ANNOTATION = '@&#91;\d+&#93;@U';
    public const SANITIZE_HTML = '@(<.*>)(.*)(</.*>)@U';
    public const OPTIONS = [
        [
            "name" => "mot",
            "description" => "Le mot à chercher",
            "type" => OptionType::STRING,
            "required" => true,
        ],
    ];


    public static function execute(Request $request): void
    {
        $interaction = json_decode($request->getBody());
        $mot = $interaction->data->options[0]->value;
        if (strlen($mot) > 300) {
            $embed = new Embed(description: "C'est pas un vrai mot ça, j'ai même pas besoin d'ouvrir un dico pour le savoir !");
            self::reply(embeds: [$embed], flags: Flag::EPHEMERAL->value);
            return;
        }
        self::reply(type: InteractionCallback::DEFERRED_CHANNEL_MESSAGE_WITH_SOURCE);
        $definition = self::getWord($mot, 1);
        if (is_null($definition)) {
            $embed = new Embed(description: "Je n'ai pas trouvé la définition de **$mot**");
            self::updateReply(request: $request, embeds: [$embed]);
            return;
        }

        self::updateReplyWithMessage(request: $request, message: $definition);
    }

    public static function getWord(string $mot, int $page = 1): ?Message
    {
        $mot = urlencode(strtolower($mot));
        $guzzleCLient = new Client();
        try {
            $response = $guzzleCLient->request(
                'POST',
                "https://api-definition.fgainza.fr/app/api_wiki.php",
                [
                    'headers' => [
                        'Accept' => "application/json",
                        'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
                    ],
                    'body' => "motWiki=$mot",
                ]
            );
            $definitions = json_decode($response->getBody()->getContents(), true) ?: null;
        } catch (ClientException $e) {
            return null;
        }

        if (is_null($definitions) || !empty($definitions['error'])) {
            return null;
        }

        $wordIndex = $page - 1;

        $definition = '';
        if (is_array($definitions['natureDef'][$wordIndex])) {
            foreach ($definitions['natureDef'][$wordIndex][0] as $def) {
                if (is_string($def)) {
                    $definition .= "- $def\n";
                }
            }
        }
        /** @var string */
        $definition = preg_replace(self::ANNOTATION_LINK, '', $definition);
        $definition = preg_replace(self::LINK_CONVERSION, '[$2]($1)', $definition);
        while (preg_match(self::REPAIR_LINK_SPACE, $definition)) {
            $definition = preg_replace(self::REPAIR_LINK_SPACE, '(https://$1%20$3)', $definition);
        }
        $definition = preg_replace(self::INVISIBLE_UNICODE, ' ', $definition);
        $definition = preg_replace(self::SANITIZE_HTML, '$2', $definition);
        $definition = preg_replace(self::ANNOTATION, '', $definition);
        $embed = new Embed(title: ucfirst($definitions['motWiki']));
        $embed->addField('Genre', is_array($definitions['genre'][$wordIndex]) ? implode(" ", $definitions['genre'][$wordIndex]) : $definitions['genre'][$wordIndex])
            ->addField('Definition', $definition);
        $embed->addField('Page', "$page/" . count($definitions['nature']));

        $message = new Message(embeds: [$embed]);
        if (count($definitions['nature']) > 1) {
            $message->addComponentsRow()
                ->addButton(style: ButtonStyle::SECONDARY, label: '⇦', custom_id: 'PreviousDefinition')
                ->addButton(style: ButtonStyle::SECONDARY, label: '⇨', custom_id: 'NextDefinition');
        }
        return $message;
    }
}
