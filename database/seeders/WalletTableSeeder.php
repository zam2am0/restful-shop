<?php

namespace Database\Seeders;
use App\Models\User;
use App\Models\Wallet;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WalletTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // Get all users
        $users = User::all();

        foreach ($users as $user) {
            // Create a wallet for each user if it doesn't already exist
            if (Wallet::where('user_id', $user->id)->doesntExist()) {
                Wallet::create([
                    'user_id' => $user->id,
                    'Balance' => 0.00, // Set the initial balance as needed
                ]);
            }
        }
    }
}
