<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResponsesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('responses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bot_id')->unsigned()->index();
            $table->foreign('bot_id')->references('id')->on('bots')->onDelete('cascade');
            $table->string('command')->index();
            $table->string('pattern')->index();
            $table->enum('response_type', ['text', 'image', 'sticker', 'video', 'audio', 'document', 'location', 'voice', 'external'])->default('text')->index();
            $table->text('response_data');
            $table->string('plugin_namespace')->default('Telebot\Plugins');
            $table->enum('as_quote', ['y', 'n'])->default('n')->index();
            $table->enum('preview_links_if_any', ['y', 'n'])->default('n')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('responses');
    }
}
