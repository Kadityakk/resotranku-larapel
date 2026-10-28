<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Seeds the menu categories. Safe to re-run.
     */
    public function run(): void
    {
        $categories = [
            'Makanan' => 'Hidangan utama dan lauk',
            'Minuman' => 'Minuman dingin dan hangat',
        ];

        foreach ($categories as $name => $description) {
            Category::updateOrCreate(
                ['cat_name' => $name],
                ['description' => $description],
            );
        }
    }
}
