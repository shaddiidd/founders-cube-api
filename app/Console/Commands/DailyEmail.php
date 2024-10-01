<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Mail;

class DailyEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:daily-email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::where('user_type', 'user')->where('active', true)->where('number_of_sent_auto_emails', '<', 3)->where('special_member', false)->whereDate('created_at', '!=', \Carbon\Carbon::today()->toDateString())->get();

        $subjects = [
            "Book a FREE Consultation Session with Sari Awwad",
            "What to Expect from The Founders Cube",
            "Your Safety & Wellbeing - What is it Like in The Founders Cube Community?",
        ];

        foreach ($users as $user) {
            $number_of_sent_auto_emails = $user->number_of_sent_auto_emails;
            if ($number_of_sent_auto_emails == 3) {
                continue;
            }

            $number_of_sent_auto_emails++;

            $data = [
                'name' => $user->full_name,
            ];
            $emails = [$user->email];

            Mail::send('email' . $number_of_sent_auto_emails, $data, function($message) use($emails, $number_of_sent_auto_emails, $subjects) {
                $message->to($emails)->subject($subjects[$number_of_sent_auto_emails - 1]);
                $message->from('noreply@mail.thefounderscube.com', 'The Founders Cube');
            });

            $user->number_of_sent_auto_emails++;
            $user->save();
        }
    }
}
