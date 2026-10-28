<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Fills the menu only when it is empty, so re-running the seeder never
     * duplicates the catalogue.
     */
    public function run(): void
    {
        if (Item::exists()) {
            return;
        }

        Item::factory()->count(12)->create();
    }
}
