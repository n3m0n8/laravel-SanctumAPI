<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            // here is our Primary key for this table - uniqu identifier
            $table->id();
            //here we fill out more details relating to our tasks tables in the database. includion obther key-column fields.
            //here we insert our foreign key of user id... incoming/attached into the users table that has been automatically generated by eloquent during our initial migration. 
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->string('description');
            // on the priority key-column value, we chain a default directive meth seting its default value on or tasks table of the DB as 'medium'
            $table->string('priority')->default('medium');
            //this foreign key will link our tasks table to our users table BUT NOTE that this is a one-to-many relationship because many tasks can belong to only one user. this one-to-many constraint will be defined below, in another of the tasks tables key-column fields which will assign this tasks table user_id key the value of a particular user identified by the user table's user_id key-value. also, if, on the users table one of these user record/row values is deleted, we want that to cascade to all of the attached tasks values that have been foreign key-related to that user. this is achieved below by using the foreign() method to poiint to the above unsigned big integer user_id values... these are imported by two chained on directive meths ->references() and on-> which detail the key-column being referenced and on which other table that is being referenced. Finally we chain on the onDelete() meth for the cascading deletion
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tasks');
    }
};