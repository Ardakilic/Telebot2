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

use Faker;

class ExamplePlugin
{
    private $responseData;
    private $request;
    private $config;
    private $rawInput; //User's input
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
        $faker = Faker\Factory::create();

        //For what to return, you can refer to Telebot.php or Telegram API
        return [
            'name' => 'text',
            'contents' => 'Hello human, ' . $faker->text,
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
}
