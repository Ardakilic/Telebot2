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
    private $responseData, //The response row from SQL 
        $request; //Bot's request data as array

    public function __construct($responseData, $request)
    {
        $this->responseData = $responseData;
        $this->request = $request;
    }

    public function setResponse()
    {
        $faker = Faker\Factory::create();

        //For what to return, you can refer to Telebot.php or Telegram API
        return [
            'name' => 'text',
            'contents' => 'Hello human, ' . $faker->text,
        ];
    }

    //The endpoint of Telegram, this defines how the message will be sent
    public function setEndpoint()
    {
        return 'sendMessage';
    }
}
