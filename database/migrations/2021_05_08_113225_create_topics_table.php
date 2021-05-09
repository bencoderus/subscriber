<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTopicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->timestamps();
        });

        $this->seedData();
    }

    /**
     * Seed topics for testing.
     *
     * @return void
     */
    private function seedData()
    {
        $topics = ['Topic1', 'Topic2'];

        $records = collect($topics)->map(function ($topic) {
            return [
                'title' => $topic,
                'slug' => Str::slug($topic),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });

        DB::table('topics')->insert($records->toArray());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('topics');
    }
}
