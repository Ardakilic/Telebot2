<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\BotRepositoryInterface;
use GuzzleHttp\Client as Guzzle;

class CreateBotCommand extends Command
{
    private $bot;

    public function __construct(BotRepositoryInterface $bot)
    {
        parent::__construct();

        $this->bot = $bot;
    }

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'bot:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Bot';

    /**
     * Execute the console command.
     */
    public function fire()
    {
        do {
            $name = $this->ask('Please enter the bot\'s name, without the leading @ character. This command will keep prompting until you set a unique name for your bot');
        } while ($this->bot->existsWithName($name));

        $token = $this->ask('Great! Now paste the token for the "' . $name . '"');

        $bot = $this->bot->create($name, $token);

        $this->info('You have successfully created a bot, now a webhook is being set..');

        $client = new Guzzle();
        $response = $client->get('https://api.telegram.org/bot' . $bot->token . '/setWebhook?url=' . env('APP_URL') . '/hook/' . $bot->id);
        $body = json_decode((string)$response->getBody(), true);
        if ($body['result'] === true && $body['ok'] === true) {
            $this->info('Success! You have set the bot hook URL successfully!');
        } else {
            $this->error('Whoops, there has been an error while setting the bot to Telegram.');
        }
        $this->info('The message from Telegram API: ' . $body['description']);
    }
    
    public function handle() {
        return $this->fire();
    }
}
