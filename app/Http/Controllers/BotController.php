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
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

class BotController extends Controller
{
    private $bot;

    /**
     * Create a new controller instance.
     * @param $bot BotRepositoryInterface bot's repository interface
     */
    public function __construct(BotRepositoryInterface $bot)
    {
        $this->bot = $bot;
    }

    /**
     * The response method for the bots
     *
     * @param $id
     * @param Request $request
     * @return string
     */
    public function response($id, Request $request)
    {
        $id = intval($id);

        //Find and bring the bot with responses from the data source
        $bot = $this->bot->findUsingId($id);
        if (!$bot) {
            //abort(404, 'Not Found');
            return 'not found'; //not 404, else telegram api will spam like crazy
        }

        //Initialize the Telegram Bot class witg Bot's data and Telegram's request
        $telebot = new Telebot($bot, $request->all(), [
            'storage_path' => storage_path(),
            'default_response' => env('DEFAULT_RESPONSE', 'Sorry, could you please repeat that?'),
            'global_config' => config()->all(),
            'global_env' => $_ENV,
        ]);

        //There are the non-response events such as a user signs into the group etc. IF bot listens everything.
        //We should return something for the api, else it may keep pinging the server
        if (!$telebot->canSendMessage()) {
            return 'Sorry, the bot can\'t send message for this event';
        }

        //Let's take the response from Telebot Class
        $botResponse = $telebot->setResponse();

        //canSendMessage is also here because external bots can refuse to response,
        //and it's understood only after trying to set response
        if (!$botResponse || !$telebot->canSendMessage()) {
            return 'OK';
        }

        //Now let's send!
        //We don't want errors showing to Telegram API, or else it'll keep pinging.
        //e.g: a response to a deleted message etc.
        //We'll catch them and do nothing for the time being
        try {
            $client = new Guzzle();
            $client->post(
                'https://api.telegram.org/bot' . $bot['token'] . '/' . $telebot->getEndpoint($botResponse['type']),
                [
                    'multipart' => $botResponse['data'],
                ]
            );
        } catch (ClientException $e) {
        } catch (RequestException $e) {
        } catch (\Exception $e) {
        }

        //Telegram wants to see a response in the end, so here goes:
        return 'OK';
    }
}
