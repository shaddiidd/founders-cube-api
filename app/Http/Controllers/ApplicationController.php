<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\VerificationEntry;
use App\Helpers\GeneralHelpers;
use Mail;

class ApplicationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email'),
                Rule::unique('applications', 'email'),
            ],
            'phone' => [
                'required',
                'string',
                Rule::unique('users', 'phone_number'),
                Rule::unique('applications', 'phone'),
            ],
            'country' => 'required|string',
            'url' => 'string|nullable',
            'years_of_experience' => 'required|string',
            'business_outline' => 'required|string|min:299',
            'educational_background' => 'required|string',
            'professional_affiliations' => 'nullable',
            'strengths' => 'required|string|min:149',
            'reasons_to_join' => 'required|string|min:149',
            'referred_by' => 'nullable',
        ]);

        $referral_code = $request->referred_by;
        if ($referral_code) {
            $ref = User::where('referral_code', $referral_code)->first();
            if (!$ref) {
                return response()->json(['message' => 'User not found'], 404);
            }
        }

        $application = Application::create($request->all());

        $data = [
            'name' => $application->full_name,
            'id' => $application->id,
        ];
        $emails = [$application->email];

        Mail::send('applicationackemail', $data, function($message) use($emails) {
            $message->to($emails)->subject('The Founders Cube - Application Acknowledged');
            $message->from('noreply@mail.thefounderscube.com', 'The Founders Cube');
        });
        // Mail::send('newapplicationrecieved', $data, function($message) {
        //     $message->to("abdullah.shadid49@gmail.com")->subject('The Founders Cube - New User Application');
        //     $message->from('noreply@mail.thefounderscube.com', 'The Founders Cube');
        // });

        return response("Sent", 201);
    }


    // ADMIN
    public function index()
    {
        $applications = Application::select('id', 'full_name', 'status', 'created_at')->orderBy('created_at', 'desc')->get();
        return response($applications, 200);
    }
    public function referrals()
    {
        $users = User::select('id','full_name')->get();
        return response($users,200);
    }
    public function homeIndex()
    {
        $applications = Application::select('id', 'full_name', 'status')
            ->orderBy('created_at', 'desc')
            ->take(2)
            ->get();
    
        return response($applications, 200);
    }

    public function confirm($id)
    {
        $application = Application::find($id);

        if (!$application) {
            return response()->json(['message' => 'Application not found'], 404);
        }

        $application->status = 'Confirmed';

        $user = User::create([
            'full_name' => $application->full_name,
            'email' => $application->email,
            'country' => $application->country,
            'url' => $application->url,
            'phone_number' => $application->phone,
            'application_id' => $application->id,
            'referral_code' => substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 6)), 0, 6),
        ]);
        $application->user_id = $user->id;
        $application->save();

        $entry = new VerificationEntry;
        $entry->email = $application->email;
        $entry->verification_token = 'ver_tok_' . GeneralHelpers::generate_random_string(128);
        $entry->expires_at = null;
        $entry->save();

        $href = GeneralHelpers::WEB_APP_URL . '/reset-password/new-user?t=' . $entry->verification_token;

        $data = [
            'href' => $href,
            'name' => $application->full_name,
        ];
        $emails = [$application->email];

        Mail::send('setpasswordmail', $data, function($message) use($emails) {
            $message->to($emails)->subject('The Founders Cube - Continue Setting Up Your Account');
            $message->from('noreply@mail.thefounderscube.com', 'The Founders Cube');
        });

        return response()->json($application, 200);
    }

    public function decline($id)
    {
        $application = Application::find($id);

        if (!$application) {
            return response()->json(['message' => 'Application not found'], 404);
        }

        $application->status = 'Declined';
        $application->save();

        $data = [
            'name' => $application->full_name,
        ];
        $emails = [$application->email];

        Mail::send('applicationdeclinedemail', $data, function($message) use($emails) {
            $message->to($emails)->subject('The Founders Cube - Application Declined');
            $message->from('noreply@mail.thefounderscube.com', 'The Founders Cube');
        });

        return response()->json(['message' => 'Application declined'], 200);
    }

    public function show($id)
    {
        $application = Application::find($id);
    
        if (!$application) {
            return response()->json(['message' => 'Application not found'], 404);
        }
    
        $applicationData = $application->toArray();
    
        // Check if the application has a referral code
        if ($application->referred_by) {
            // Find the user with the referral code
            $referringUser = User::where('referral_code', $application->referred_by)->first();
    
            // If the referring user is found, replace the referral code with the full name
            if ($referringUser) {
                $applicationData['referred_by'] = $referringUser->full_name;
            }
        }
    
        // If the application is associated with a user, include the 'verified' field
        if ($application->user_id) {
            $applicationData['verified'] = $application->user->verified;
        }
    
        return response()->json($applicationData, 200);
    }
    

}
