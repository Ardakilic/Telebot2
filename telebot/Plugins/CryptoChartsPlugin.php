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

class CryptoChartsPlugin
{
    private $responseData, //The response row from SQL
        $request, //Bot's request data as array
        $config, //The whole config, along with plugin specific configuration
        $rawInput; //User's input
    // $config['global_config'] returns the Lumen configuration array
    // $config['global_env'] returns the environment variables

    // Bot-specific
    private $chartData, $canRespond;

    public function __construct($responseData, $request, $config, $rawInput)
    {
        $this->responseData = $responseData;
        $this->request = $request;
        $this->config = $config;
        $this->rawInput = $rawInput;

        // Bot-specific
        $this->imageURL = false;
        $this->canRespond = false;
        $this->chartData = null;

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
                'contents' => 'Wrong input. Structure: /command [currency] [compare] [timespan] [theme] Example: /chart ppc , /chart ppc usd , /chart2 ppc usdt 7d candlestick. Refer to https://cryptohistory.org/ for details',
            ];
        }

        return [
            'name' => 'photo',
            'contents' => $this->chartData,
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
        return 'sendPhoto';
    }


    /**
     * Checks the user input that whether the bot can respond
     * Also, this method sets the parameters for Google TTS engine
     *
     * @return bool
     */
    private function prepareAndCheckRespond()
    {

        $parameters = [
            'currency' => 'eth', //can be ltc, ppc, nmc, etc. etc.
            'compare' => 'btc', //can be btc or usdt
            'timespan' => '24h', //can be 1y, 30d, 7d, or 24h
            'theme' => 'candlestick', //can be candlestick, dark, light, sparkling
        ];

        // raw input is like: /command currency compare timespan theme
        // $input variable is "currency compare timespan theme" or whatever available.

        $inputParams = array_map('trim', explode(' ', $this->rawInput));

        // Now let's set parameters
        // 1st one is always here, check done above:
        $parameters['currency'] = $inputParams[0];

        // compare: non mandatory
        if (isset($inputParams[1])) {
            // Common mistake: if compare is usd, let's make it usdt
            if ($inputParams[1] == 'usd') {
                $inputParams[1] = 'usdt';
            }
            $parameters['compare'] = $inputParams[1];
        }

        if (isset($inputParams[2])) {
            $parameters['timespan'] = $inputParams[2];
        }
        if (isset($inputParams[3])) {
            $parameters['theme'] = $inputParams[3];
        }

        // Source code of charts, in case it goes down: https://github.com/seigler/neat-charts
        // Or my fork: https://github.com/Ardakilic/neat-charts just in case

        $chartData = fopen('https://cryptohistory.org/charts/' . $parameters['theme'] . '/' . $parameters['currency'] . '-' . $parameters['compare'] . '/' . $parameters['timespan'] . '/png', 'rb');
        if (!$chartData) {
            return false;
        }
        $this->chartData = $chartData;
        $this->canRespond = true;

        return true;
    }


}
