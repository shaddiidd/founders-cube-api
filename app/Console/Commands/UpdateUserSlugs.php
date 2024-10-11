<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Str;

class UpdateUserSlugs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:update-slugs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate slugs for all existing users';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $users = User::whereNull('slug')->get(); // Get users without a slug

        foreach ($users as $user) {
            $user->slug = Str::slug($user->name); // Generate slug based on the user's name
            $user->save(); // Save the user with the new slug
            $this->info("Slug generated for user: {$user->name} ({$user->slug})");
        }

        $this->info('All user slugs have been updated.');
        return 0;
    }
}
