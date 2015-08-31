<?php

use Illuminate\Database\Seeder;
use Mypleasure\Collection;
use Mypleasure\User;
use Carbon\Carbon;
use \DB;

class CollectionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Deleting Collection table...');
        DB::table('collections')->delete();

        $this->command->info('Seeding Collection table...');
        $davy = User::where('username', 'davy')->first();
        $morgane = User::where('username', 'morgane')->first();
        $max = User::where('username', 'max')->first();

        $set1 = [
          ['name' => 'collection de davy 1', 'slug' => 'collection-de-davy-1', 'private' => false, 'user_id' => $davy->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
          ['name' => 'collection de davy 2', 'slug' => 'collection-de-davy-2', 'private' => false, 'user_id' => $davy->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
          ['name' => 'collection de davy 3', 'slug' => 'collection-de-davy-3', 'private' => true, 'user_id' => $davy->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
        ];

        $set2 = [
          ['name' => 'collection de morgane 1', 'slug' => 'collection-de-morgane-1', 'private' => false, 'user_id' => $morgane->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
          ['name' => 'collection de morgane 2', 'slug' => 'collection-de-morgane-2', 'private' => true, 'user_id' => $morgane->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
        ];

        $set3 = [
          ['name' => 'collection de max 1', 'slug' => 'collection-de-max-1', 'private' => false, 'user_id' => $max->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
          ['name' => 'collection de max 2', 'slug' => 'collection-de-max-2', 'private' => true, 'user_id' => $max->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
        ];

        Collection::insert($set1);
        Collection::insert($set2);
        Collection::insert($set3);
    }
}