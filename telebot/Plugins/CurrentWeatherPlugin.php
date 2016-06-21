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

use GuzzleHttp\Client;

class CurrentWeatherPlugin
{
    private $responseData, //The response row from SQL
        $request, //Bot's request data as array
        $config, //The whole config, along with plugin specific configuration
        $rawInput; //User's input
    // $config['global_config'] returns the Lumen configuration array
    // $config['global_env'] returns the environment variables

    public function __construct($responseData, $request, $config, $rawInput)
    {
        $this->responseData = $responseData;
        $this->request = $request;
        $this->config = $config;
        $this->rawInput = $rawInput;
    }

    /**
     * The response data for Telegram API
     *
     * @return array
     */
    public function setResponse()
    {

        $weatherData = json_decode($this->getWeatherInfoFromAPI(), true);
        //http://openweathermap.org/weather-conditions
        if (!isset($weatherData['main']['temp'])) {
            return [
                'name' => 'text',
                'contents' => 'No weather data found',
            ];
        }

        return [
            'name' => 'text',
            'contents' => 'Ahoy, The weather at ' . $weatherData['name'] . ' is ' . $weatherData['main']['temp'] . '°C. It\'s ' . $weatherData['weather'][0]['main'] . ' (' . $weatherData['weather'][0]['description'] . ') Minimum is ' . $weatherData['main']['temp_min'] . '°C, maximum is ' . $weatherData['main']['temp_max'] . '°C.',
        ];

    }


    /**
     * The endpoint of Telegram, this defines how the message will be sent
     *
     * @return string
     */
    public function setEndpoint()
    {
        return 'sendMessage';
    }

    /**
     * The API response from OpenWeatherMap
     * @return string
     */
    private function getWeatherInfoFromAPI()
    {
        $client = new Client();
        $response = $client->get('http://api.openweathermap.org/data/2.5/weather?q=' . $this->stripCityFromInput() . '&appid=' . $this->config['global_env']['PLUGIN_OPENWEATHERMAP_API_KEY'] . '&units=metric');
        return $response->getBody()->getContents();
    }

    /**
     * The request string holds both parameter and city name, so we split the parameter from user's input
     * @return string
     */
    private function stripCityFromInput()
    {
        return trim(str_replace($this->responseData['pattern'], '', $this->rawInput));
    }
}
