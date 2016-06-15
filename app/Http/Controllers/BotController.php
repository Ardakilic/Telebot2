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

namespace App\Http\Controllers;

use App\Repositories\BotRepositoryInterface;
use Telebot\Telebot;
use Illuminate\Http\Request;
use GuzzleHttp\Client as Guzzle;

class BotController extends Controller
{
    private $bot;

    /**
     * Create a new controller instance.
     */
    public function __construct(BotRepositoryInterface $bot)
    {
        $this->bot = $bot;
    }

    public function response($id, Request $request)
    {
        $id = intval($id);

        //Find and bring the bot with responses from the data source
        $bot = $this->bot->findUsingId($id);
        if (!$bot) {
            abort(404, 'Not Found');
        }

        //Initialize the Telegram Bot class witg Bot's data and Telegram's request
        $telebot = new Telebot($bot, $request->all(), [
            'storage_path' => storage_path(),
            'default_response' => env('DEFAULT_RESPONSE', 'Sorry, could you please repeat that?'),
        ]);

        //There are the non-response events such as a user signs into the group etc.
        //We should return something for the bot else it may keep pinging the server
        if (!$telebot->canSendMessage()) {
            return 'Sorry, the bot can\'t send message for this event';
        }

        //Let's take the response from Telebot Class
        $botResponse = $telebot->setResponse();

        if(!$botResponse) {
            return 'OK';
        }

        //Now let's send!
        $client = new Guzzle();
        $client->post(
            'https://api.telegram.org/bot' . $bot['token'] . '/' . $telebot->getEndpoint($botResponse['type']),
            [
                'multipart' => $botResponse['data'],
            ]
        );

        //Telegram wants to see a response in the end, so here goes:
        return 'OK';
    }
}
