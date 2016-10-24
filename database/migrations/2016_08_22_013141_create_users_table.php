<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('login', 64)->unique();
            $table->string('display_name')->nullable();
            $table->string('email')->unique();
            $table->string('password', 60);
            $table->timestamps();
            $table->softDeletes();
            $table->string('person', 256)->nullable();
            $table->string('address', 256)->nullable();
            $table->string('phone', 256)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }
}
