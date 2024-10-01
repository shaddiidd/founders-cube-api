<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Mail;
use Carbon\Carbon;

class SubscriptionChecker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:subscription-checker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks for subscriptions ending soon and sends notifications.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $usersToEnd = User::where('user_type', 'user')
            ->where(function ($query) {
                $query->where('active', true)
                    ->orWhere('is_subscription_active', true);
            })
            ->where('subscription_ends_at', '<=', Carbon::now()->toDateTimeString())
            ->get(); // Retrieve the collection of users

        foreach ($usersToEnd as $userToEnd) {
            $userToEnd->update([
                'active' => false,
                'is_subscription_active' => false,
            ]);

            $data = [
                'id' => $userToEnd->id,
                'name' => $userToEnd->full_name,
            ];

            // Send email to the user
            Mail::send('subscriptionendedemail', $data, function($message) use ($userToEnd) {
                $message->to($userToEnd->email)->subject('The Founders Cube - Subscription Ended');
                $message->from('noreply@mail.thefounderscube.com', 'The Founders Cube');
            });

            // Send email to the admin
            Mail::send('subscriptionendedemailadmin', $data, function($message) {
                $message->to(['sari.awwad@gmail.com', 'abdullah.shadid49@gmail.com'])->subject('The Founders Cube - Subscription Ended');
                $message->from('noreply@mail.thefounderscube.com', 'The Founders Cube');
            });
        }

        $activeSubscriptions = User::where('user_type', 'user')
            ->where('active', true)
            ->where('is_subscription_active', true)
            ->whereDate('subscription_ends_at', '>', Carbon::today()->toDateString())
            ->get();

        foreach ($activeSubscriptions as $activeUser) {
            $now = Carbon::now();
            $subscriptionEndsAt = Carbon::parse($activeUser->subscription_ends_at);
            $difference = $subscriptionEndsAt->diffInSeconds($now);
            $numDaysLeft = intval($difference / (60 * 60 * 24));

            if (in_array($numDaysLeft, [1, 10, 30])) {
                $data = [
                    'name' => $activeUser->full_name,
                    'days' => $numDaysLeft,
                ];

                // Send email to the user
                Mail::send('subscriptionreminderemail', $data, function($message) use ($activeUser) {
                    $message->to($activeUser->email)->subject('The Founders Cube - Subscription Reminder');
                    $message->from('noreply@mail.thefounderscube.com', 'The Founders Cube');
                });

                // Send email to the admin
                Mail::send('subscriptionreminderemailadmin', $data, function($message) {
                    $message->to(['sari.awwad@gmail.com', 'sari.awwad@gmail.com'])->subject('The Founders Cube - Subscription Reminder');
                    $message->from('noreply@mail.thefounderscube.com', 'The Founders Cube');
                });
            }
        }
    }
}
