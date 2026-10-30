<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * Fills a fresh database with what you need to try the app. Run with:
 *
 *   php artisan migrate --seed          (first time)
 *   php artisan migrate:fresh --seed    (wipe everything and start over)
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->setUpPassport();

        // The demo account. Change the password (or remove this) before going live.
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'password' => 'password'] // hashed by the User model's 'password' => 'hashed' cast
        );

        $this->call(ProductSeeder::class);
    }

    /**
     * Signing in needs Passport's encryption keys (storage/oauth-*.key) and a "personal access
     * client" row. Both are made here once, so setup is just `migrate --seed`.
     */
    private function setUpPassport(): void
    {
        if (!file_exists(storage_path('oauth-private.key'))) {
            Artisan::call('passport:keys');
        }

        if (!DB::table('oauth_personal_access_clients')->exists()) {
            Artisan::call('passport:client', [
                '--personal' => true,
                '--name' => config('app.name') . ' Personal Access Client',
                '--no-interaction' => true,
            ]);
        }
    }
}
