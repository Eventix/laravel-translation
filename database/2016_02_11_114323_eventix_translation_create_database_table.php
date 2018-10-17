<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EventixTranslationCreateDatabaseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('translations', function(Blueprint $table) {
            $table->string('company_id');
            $table->string('namespace')->nullable();
            $table->string('locale');
            $table->string('group');
            $table->string('name');
            $table->string('value');

            $table->foreign('company_id')->references('guid')->on('companies')->onDelete('cascade');
            $table->unique(['company_id', 'locale', 'group', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('translations');
    }
}
