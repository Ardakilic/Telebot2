# Telebot2

This app is a very simple Telegram PHP API for newly announced [Telegram Bots](https://telegram.org/blog/bot-revolution).

This PHP Script is totally rewrite of [Telegram-bot-php](https://github.com/Ardakilic/Telegram-bot-php/).

Features
---------

* By installing this script, you can have a working Telegram Bot within minutes!
* This script only has one route to listen Post Request from Telegram API.
* This script can handle multiple bots from same route. Different hooks will be different route parameters. I'm already hosting two different bots with the same application.
* This app can send **messages**. It can also send **photos**, **stickers**, **video** and **audio** files, **documents** and **location**s.
* Bot works on both private chats and groups.
* The bot can either quote or send the response text directly.
* You can enable or disable preview links in bots' responses.
* ***Plugin Support! You can add your plugins and call them from any namespace :party:***

Screenshots
---------
![ss1](https://i.imgur.com/BsbdkiC.png)

![ss2](https://i.imgur.com/ahbE5nJ.png)


Requirements
---------
* [Lumen's Server Requirements](https://lumen.laravel.com/docs/5.2#server-requirements),
* Curl for PHP must be enabled to use Guzzle,
* A valid SSL certificate (Telegram API requires this). As free SSL solutions, you can use [Let's Encrypt](https://letsencrypt.org), or [Cloudflare's Free Flexible SSL](https://www.cloudflare.com/ssl) or [Wosign](https://buy.wosign.com/free/) to encrypt the web traffic.
* Telegram API Token, you can get one simply with [@BotFather](https://core.telegram.org/bots#3-how-do-i-create-a-bot) with simple commands right after creating your bot.

Installation
---------
1. Set a new virtualhost and set the `public` folder as root. Don't forget to create rewrite rules, too. You can refer to Lumen's official documentation [here](https://lumen.laravel.com/docs/5.2#server-requirements)
2. Copy the app into your virtualhost.
3. `cd ` into the app's directory
4. Run `composer install` and install dependencies.
5. Copy `.env.example` to `.env` if not done already.
6. Edit the `.env.example` file and fill all of the credentials.
7. Create your Telegram bot if not already and commands and get the Access Token from Botfather.
8. Run `php artisan migrate` to create database tables.
8. Configure your Telegram Bot:
	* Run `php artisan bot:create`. It will ask some information
	* Fill the bot's name without the `@` character. So if your bot's name is `@HodorBot`, type `HodorBot`.
	* On the second prompt, fill your Bot's Token.
	* After filling both the credentials, it will
10. (Optional) Run `php artisan db:seed` to run demo seeder data, or check `database/seeds/ExampleDataSeeder.php` for example data.
11. Send your first command to your newly Telegram bot and see it in action! :smile:

Plugin Support
---------
Within the rewrite of the script from scratch, now you will be able to use your own PHP classes from any namespace and run and implement them directly!

You just need to set a PHP class like this:

```php
<?php

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
```

And a row like this in `responses` table:

```
[
    'bot_id' => 1
    'command' => 'alinti', //Without the leading slash
    'pattern' => 'naber',
    'response_type' => 'external',
    'response_data' => 'ExamlePlugin',
    'plugin_namespace' => 'Telebot\Plugins', //These two lines will call \Telebot\Plugins\ExamplePlugin Class
    'as_quote' => 'y',
    'preview_links_if_any' => 'n',
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
],
```

The above codes will produce a response such as this:

![Plugin](https://i.imgur.com/leYr7e0.png)

As you can see, the namespace is defined at it's row. You can copy and paste your plugin into `telebot/plugins` directory (it won't be tracked), or call it as a requirement from composer and just set the class name and namespace.


Notes
---------
* If there are more than one matches, a random one is chosen. So there may be more than one responses for matching patterns.
* The script checks the matching words from the bot with `preg_match()`. A code like `preg_match('/pattern/i', $userInput)` is run to match the responses with given user input.
* Location has `latitude` and `longtitude` parameters, but since there is only one `response_data` column, you have to use `|` character as delimiter. Example `location` `response_data` for Istanbul, Turkey: `41.015137|28.979530`.
* Please see `database/seeds/ExampleDataSeeder.php` for all of the examples.
* Personally, I'd suggest every response to have commands, because sometimes the @botMention, especially on the case when commands are set for it, don't work.


TODOs
---------
* Inline Bots support
* More plugins
* ?

Contributing
---------
* Fork the project,
* Do your magic,
* Send a pull request

Changelog
---------
#### 0.2.0 - release 2016-06-15
* You can now set the default response for the bot if there's no matching results.

#### 0.1.0 - release 2016-06-15
* Initial Release.

License
---------
MIT