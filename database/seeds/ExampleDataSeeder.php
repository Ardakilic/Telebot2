<?php

use Illuminate\Database\Seeder;
use App\Models\Bot;
use App\Models\Response;

class ExampleDataSeeder extends Seeder
{
    public function run()
    {
        DB::table('responses')->delete();
        DB::table('bots')->delete();

        $bot = Bot::create([
            'name' => 'demoDataBot',
            'token' => '1a2b3c4d5e6f7g8h9i0j1k',
        ]);

        $theDate = date('Y-m-d H:i:s');

        Response::insert([
            //This is for a text response for "/hello darling"
            [
                'bot_id' => $bot->id, //Id of the bot we have just created
                'command' => 'hello', //without the trailing slash
                'pattern' => 'darling', //This is checked with /pattern/i
                'response_type' => 'text', //will be a text response,
                'response_data' => 'Hello to you, too, darling!', //The response message
                'plugin_namespace' => null, //see below for details
                'as_quote' => 'y', //can be y or n
                'preview_links_if_any' => 'n', //If the "response_data" contains url, will they be previewed in chat?
                'created_at' => $theDate,
                'updated_at' => $theDate,
            ],
            [
                'bot_id' => $bot->id,
                'command' => null, //If the value is null, this will be a response for quotes like "@demoDataBot polaroid"
                'pattern' => 'polaroid',
                'response_type' => 'image', //'text', 'image', 'sticker', 'video', 'audio', 'document', 'location', 'voice'
                'response_data' => 'image.jpg', //storage/telebot/photo/image.jpg
                'plugin_namespace' => null, //see below for details
                'as_quote' => 'y', //can be y or n
                'preview_links_if_any' => 'n', //If the "response_data" contains url, will they be previewed in chat?
                'created_at' => $theDate,
                'updated_at' => $theDate,
            ],
            [
                'bot_id' => $bot->id,
                'command' => null, // /@demoDataBot sticker
                'pattern' => 'sticker',
                'response_type' => 'sticker', //'text', 'image', 'sticker', 'video', 'audio', 'document', 'location', 'voice', 'external'
                'response_data' => 'gnu.png', //storage/telebot/sticker/gnu.png
                'plugin_namespace' => null, //see below for details
                'as_quote' => 'n', //can be y or n
                'preview_links_if_any' => 'n', //If the "response_data" contains url, will they be previewed in chat?
                'created_at' => $theDate,
                'updated_at' => $theDate,
            ],
            [
                'bot_id' => $bot->id,
                'command' => 'location', // /location istanbul
                'pattern' => 'istanbul',
                'response_type' => 'location', //'text', 'image', 'sticker', 'video', 'audio', 'document', 'location', 'voice', 'external'
                'response_data' => '41.015137|28.979530', //Splitted by | character
                'plugin_namespace' => null, //see below for details
                'as_quote' => 'n', //can be y or n
                'preview_links_if_any' => 'n', //If the "response_data" contains url, will they be previewed in chat?
                'created_at' => $theDate,
                'updated_at' => $theDate,
            ],
            [
                'bot_id' => $bot->id,
                'command' => 'video', // /video bigbuckbunny
                'pattern' => 'bigbuckbunny',
                'response_type' => 'video', //'text', 'image', 'sticker', 'video', 'audio', 'document', 'location', 'voice', 'external'
                'response_data' => 'big_buck_bunny.mp4', //storage/telebot/video/image.jpg
                'plugin_namespace' => null, //see below for details
                'as_quote' => 'y', //can be y or n
                'preview_links_if_any' => 'y', //If the "response_data" contains url, will they be previewed in chat?
                'created_at' => $theDate,
                'updated_at' => $theDate,
            ],
            [
                'bot_id' => $bot->id,
                'command' => 'document', // /document human
                'pattern' => 'human',
                'response_type' => 'document', //'text', 'image', 'sticker', 'video', 'audio', 'document', 'location', 'voice', 'external'
                'response_data' => 'the_universal_declaration_of_human_rights.pdf', //storage/telebot/document/the_universal_declaration_of_human_rights.pdf
                'plugin_namespace' => null, //see below for details
                'as_quote' => 'y', //can be y or n
                'preview_links_if_any' => 'n', //If the "response_data" contains url, will they be previewed in chat?
                'created_at' => $theDate,
                'updated_at' => $theDate,
            ],
            [
                'bot_id' => $bot->id,
                'command' => 'external',
                'pattern' => 'external call',
                'response_type' => 'external', //'text', 'image', 'sticker', 'video', 'audio', 'document', 'location', 'voice', 'external'
                'response_data' => 'ExamlePlugin',
                'plugin_namespace' => 'Telebot\Plugins', //These two lines will call \Telebot\Plugins\ExamplePlugin Class
                'as_quote' => 'y', //can be y or n
                'preview_links_if_any' => 'n', //If the "response_data" contains url, will they be previewed in chat?
                'created_at' => $theDate,
                'updated_at' => $theDate,
            ],

        ]);
    }
}
