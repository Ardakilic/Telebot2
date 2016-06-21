<?php

/**
 * Telebot2
 * https://github.com/Ardakilic/Telebot2.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link        https://github.com/Ardakilic/Telebot2
 *
 * @copyright   2016 Arda Kilicdagi. (https://arda.pw/)
 * @license     http://opensource.org/licenses/MIT - MIT License
 */

namespace Telebot;

class Telebot
{
    private $botWithResponses,
        $requestData,
        $defaultResponse,
        $config,
        $globalConfig,
        $globalEnv,
        $storagePath,
        $externalEndpoint;

    public function __construct($botWithResponses, $requestData, $config)
    {
        if (!is_array($botWithResponses)) {
            throw new \Exception('Telebot class initialization parameter should be an array');
        }
        $this->botWithResponses = $botWithResponses;
        $this->requestData = $requestData;
        $this->config = $config;
        $this->globalConfig = $config['global_config'];
        $this->globalEnv = $config['global_env'];
        $this->storagePath = $config['storage_path'];
        $this->defaultResponse = 'Sorry, could you please repeat that?';
        if (isset($config['default_response']) && $config['default_response'] !== null) {
            $this->defaultResponse = $config['default_response'];
        }
        //This variable will be filled when external plugin sets it
        $this->externalEndpoint = null;
    }

    /**
     * Sets the endpoint for Bot API due to returned request from fetched response.
     *
     * @param $type string Type of response
     *
     * @return string the Endpoint for Telegram
     */
    public function getEndpoint($type)
    {
        switch ($type) {
            case 'text':
                return 'sendMessage';
                break;
            case 'image':
                return 'sendPhoto';
                break;
            case 'sticker':
                return 'sendSticker';
                break;
            case 'video':
                return 'sendVideo';
                break;
            case 'audio':
                return 'sendAudio';
                break;
            case 'document':
                return 'sendDocument';
                break;
            case 'location':
                return 'sendLocation';
                break;
            case 'voice':
                return 'sendVoice';
                break;
            case 'external':
                return ($this->externalEndpoint !== null) ? $this->externalEndpoint : 'sendMessage';
                break;
            default:
                return 'sendMessage';
                break;
        }
    }

    /**
     * This method returns the data to be sent back to the Telegram bot.
     *
     * @return array
     */
    public function setResponse()
    {
        $requestData = $this->requestData;

        //This is the output that will be sent to Telegram as an array
        $queryArray = [
            [
                'name' => 'chat_id',
                'contents' => (string)$requestData['message']['chat']['id'],
            ],
        ];

        $rawRequestFromUser = $this->stripOnlyText();

        $response = $this->bringMatchingResponse($rawRequestFromUser, $this->getCommandName());

        //If no response could be fetched from the database, let's explode the string and check by each word
        if (!$response) {
            $words = array_map('trim', explode(' ', $rawRequestFromUser));

            foreach ($words as $word) {
                $response = $this->bringMatchingResponse($word, $this->getCommandName());
                if ($response !== false) {
                    break;
                }
            }

            //If all fails, let's set the fallback message for text
            if (!$response) {

                if ($this->getCommandName() !== null) {

                    if ($this->hasMatchingCommand()) {
                        array_push($queryArray, [
                            'name' => 'text',
                            'contents' => $this->defaultResponse,
                        ]);
                    } else {
                        return false;
                    }

                } else {
                    array_push($queryArray, [
                        'name' => 'text',
                        'contents' => $this->defaultResponse,
                    ]);
                }
            }
        }

        $queryArray = array_merge($queryArray, $this->setParams($response));

        return [
            'type' => (isset($response) && $response) ? $response['response_type'] : 'text',
            'data' => $queryArray,
        ];
    }

    /**
     * If This method Sets the parameters that will be sent to Telegram.
     *
     * @param $response array the fetched data from SQL
     *
     * @return array the array of data
     */
    private function setParams($response)
    {
        //Let's set a blank array at first
        $out = [];

        //All these array keys are based on the available methods:
        //https://core.telegram.org/bots/api#available-methods
        switch ($response['response_type']) {
            case 'text':
                array_push($out, [
                    'name' => 'text',
                    'contents' => $response['response_data'],
                ]);
                break;
            case 'image':
                array_push($out,
                    [
                        'name' => 'photo',
                        'contents' => fopen($this->storagePath . '/telebot/photo/' . $response['response_data'], 'rb'),
                    ],
                    [
                        'name' => 'caption',
                        'contents' => $response['response_data'],
                    ]
                );
                break;
            case 'sticker':
                array_push($out, [
                    'name' => 'sticker',
                    'contents' => fopen($this->storagePath . '/telebot/sticker/' . $response['response_data'], 'rb'),
                ]);
                break;
            case 'video':
                array_push($out, [
                    'name' => 'video',
                    'contents' => fopen($this->storagePath . '/telebot/video/' . $response['response_data'], 'rb'),
                ]);
                break;
            case 'audio':
                array_push($out, [
                    'name' => 'audio',
                    'contents' => fopen($this->storagePath . '/telebot/audio/' . $response['response_data'], 'rb'),
                ]);
                break;
            case 'document':
                array_push($out, [
                    'name' => 'document',
                    'contents' => fopen($this->storagePath . '/telebot/document/' . $response['response_data'], 'rb'),
                ]);
                break;
            case 'location':
                $latLng = explode($response['response_data'], '|');
                array_push($out, [
                    'name' => 'location',
                    'latitude' => $latLng[0],
                    'longtitude' => $latLng[1],
                ]);
                break;
            case 'external':
                //TODO, maybe some service provider support like Laravel-specific plugins?
                $externalPlugin = '\\' . $response['plugin_namespace'] . '\\' . $response['response_data'];
                $plugin = new $externalPlugin($response, $this->requestData, $this->config, $this->stripOnlyText());
                //Set the endpoint for getEndpoint() method
                $this->externalEndpoint = $plugin->setEndpoint();
                array_push($out, $plugin->setResponse());
                break;
            default:
                array_push($out, [
                    'name' => 'text',
                    'contents' => $response['response_data'],
                ]);
                break;
        }
        if ($response['as_quote'] == 'y') {
            array_push($out, [
                'name' => 'reply_to_message_id',
                'contents' => (string)$this->requestData['message']['message_id'],
            ]);
        }
        if ($response['preview_links_if_any'] == 'n') {
            array_push($out, [
                'name' => 'disable_web_page_preview',
                'contents' => 'true',
            ]);
        }

        return $out;
    }

    /**
     * This method is to strip only the text from the requested string.
     *
     * @return string Stripped text
     */
    private function stripOnlyText()
    {
        $message = $this->requestData['message']['text'];
        if ($this->isCommand()) {
            // "/command@nameOfTheBot command" is also a valid command
            $message = str_replace('/' . $this->getCommandName() . '@' . $this->botWithResponses['name'], '', $message);
            return trim(substr($message, strpos($message, ' ') + 1));
        } else {
            return trim(str_replace('@' . $this->botWithResponses['name'], '', $message));
        }
    }

    /**
     * This method is to bring matching responses from the bot's response array.
     * In other terms, this filters the responses with matching parameters, such as SQL's LIKE.
     *
     * @param $pattern
     * @param null $command
     *
     * @return bool|mixed
     */
    private function bringMatchingResponse($pattern, $command = null)
    {
        if ($command !== null) {
            $botName = $this->botWithResponses['name'];
            $command = str_replace('@' . $botName, '', $command);
        }
        $matchingResponses = array_filter($this->botWithResponses['responses'], function ($value) use ($pattern, $command) {
            //User can also send messages such as "/command@nameOfTheBot response"
            if ($command !== null && $value['command'] != $command) {
                return false;
            }

            if (!strlen(trim($value['pattern'])) || $value['pattern'] === null) {
                return true;
            }

            return strlen($pattern) ? (preg_match('/' . preg_quote($value['pattern'], '/') . '/i', $pattern) === 1) : false;
        });

        if (count($matchingResponses) === 0) {
            return false;
        }

        //Pick a random element from array
        $random = array_rand($matchingResponses);

        return $matchingResponses[$random];
    }

    /**
     * This returns whether the request is through a command or not.
     *
     * @return bool
     */
    private function isCommand()
    {
        return isset($this->requestData['message']['entities']) && $this->requestData['message']['entities'][0]['type'] == 'bot_command';
    }

    /**
     * This method returns the command name, if it's not a command it returns a string instead.
     *
     * @return null|string
     */
    private function getCommandName()
    {
        if ($this->isCommand()) {
            $message = $this->requestData['message']['text'];

            return trim(substr($message, 1, strpos($message, ' ')));
        } else {
            return null;
        }
    }

    /**
     * This method checks whether the bot can send a response.
     * The response should have text, it shouldn't be a new chat participant information,
     * and if it's a command, it should be a matching command for the bot responses.
     *
     * @return bool
     */
    public function canSendMessage()
    {
        return isset($this->requestData['message']['text'])
        && !isset($this->requestData['message']['new_chat_member'])
        && !isset($this->requestData['message']['new_chat_participant']);
    }

    /**
     * This method checks whether the bot has responses with given command parameter
     *
     * @return bool
     */
    private function hasMatchingCommand()
    {
        if ($this->getCommandName() === null) {
            return false;
        } else {
            $commandName = $this->getCommandName();
            //User can also send messages such as "/command@nameOfTheBot response"
            $commandName = str_replace('@' . $this->botWithResponses['name'], '', $commandName);
            $matchingResponses = array_filter($this->botWithResponses['responses'], function ($value) use ($commandName) {
                return $value['command'] == $commandName;
            });

            //If no response is found, return false
            if (count($matchingResponses) === 0) {
                return false;
            }
            return true;
        }
    }
}
