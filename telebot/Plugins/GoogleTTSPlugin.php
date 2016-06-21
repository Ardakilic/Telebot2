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

namespace Telebot\Plugins;

class GoogleTTSPlugin
{
    private $responseData, //The response row from SQL
        $request, //Bot's request data as array
        $config, //The whole config, along with plugin specific configuration
        $rawInput; //User's input
    // $config['global_config'] returns the Lumen configuration array
    // $config['global_env'] returns the environment variables

    // Bot-specific
    private $language, $userInput, $ttsAudio, $canRespond;

    public function __construct($responseData, $request, $config, $rawInput)
    {
        $this->responseData = $responseData;
        $this->request = $request;
        $this->config = $config;
        $this->rawInput = $rawInput;

        // Bot-specific
        $this->language = null;
        $this->userInput = null;
        $this->ttsAudio = null;
        $this->canRespond = false;

        // Set variables and check respond
        $this->prepareAndCheckRespond();
    }

    /**
     * The response data for Telegram API
     *
     * @return array
     */
    public function setResponse()
    {

        if (!$this->canRespond) {
            return [
                'name' => 'text',
                'contents' => 'Wrong input. Your input should be like "lang stringforvoice", for lang parameters, please refer to https://gist.github.com/Ardakilic/6edb6c64f989fbfb182fc4ebdff34148',
            ];
        }

        return [
            'name' => 'audio',
            'contents' => $this->ttsAudio,
        ];
    }


    /**
     * The endpoint of Telegram, this defines how the message will be sent
     *
     * @return string
     */
    public function setEndpoint()
    {
        if (!$this->canRespond) {
            return 'sendMessage';
        }
        return 'sendAudio';
    }


    /**
     * Checks the user input that whether the bot can respond
     * Also, this method sets the parameters for Google TTS engine
     *
     * @return bool
     */
    private function prepareAndCheckRespond()
    {
        $offsetOfFirstSpace = strpos($this->rawInput, ' ');
        if ($offsetOfFirstSpace === false) {
            return false;
        }

        $stringForSpeech = substr($this->rawInput, $offsetOfFirstSpace + 1);
        if (strlen($stringForSpeech) === 0) {
            return false;
        }

        $this->language = substr($this->rawInput, 0, $offsetOfFirstSpace);
        $this->userInput = $stringForSpeech;

        $ttsAudio = @fopen('https://translate.google.com/translate_tts?ie=UTF-8&total=1&idx=0&client=tw-ob&q=' . urlencode($this->userInput) . '&tl=' . $this->language, 'rb');
        if (!$ttsAudio) {
            return false;
        }
        $this->ttsAudio = $ttsAudio;

        $this->canRespond = true;

        return true;
    }


}
