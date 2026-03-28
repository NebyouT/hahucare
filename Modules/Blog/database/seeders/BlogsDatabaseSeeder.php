<?php

namespace Modules\Blog\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Blog\Models\Blog;

class BlogsDatabaseSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        // Call the permission seeder
        $this->call(BlogPermissionSeeder::class);
    }
}