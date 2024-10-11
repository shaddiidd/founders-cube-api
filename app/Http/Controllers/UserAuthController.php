<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Package;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use App\Helpers\GeneralHelpers;
use Validator;
use App\Models\VerificationEntry;
use Mail;

class UserAuthController extends Controller
{

    public function index(Request $request)
    {
        $users = User::select(
            'id',
            'full_name',
            'bio',
            'profile_picture',
            'industry',
            'verified',
            'special_member',
            'is_subscription_active',
            'subscription_ends_at',
            'company',
        )
            ->where(function ($query) {
                $query->where('active', true)
                    ->orWhere('special_member', true);
            })
            ->orderByRaw('special_member DESC, verified DESC')
            ->get();
        $users->each->append('profile_pic');
        return response($users, 200);
    }
    public function adminIndex(Request $request)
    {
        $users = User::select('id', 'full_name', 'bio', 'profile_picture', 'industry', 'verified', 'special_member', 'is_subscription_active', 'subscription_ends_at', 'active')
            ->orderByRaw('special_member DESC, verified DESC')
            ->get();
        $users->each->append('profile_pic');
        return response($users, 200);
    }

    public function specialMembers()
    {
        $users = User::select('id', 'full_name', 'bio', 'profile_picture', 'industry', 'verified', 'special_member', 'is_subscription_active', 'subscription_ends_at', 'active')
            ->where('special_member', true)
            ->orWhere('verified', true)
            ->get();
        $users->each->append('profile_pic');
        return response($users, 200);
    }
    public function storeSpecialMembers(Request $request)
    {
        $options = [
            'Accounting',
            'AI Apps',
            'Architecture',
            'AR/VR in Architecture',
            'Artificial Intelligence',
            'Automotive',
            'Beauty Tech',
            'Biotechnology',
            'Blockchain',
            'Childcare Service',
            'Children Services',
            'Clean Energy',
            'Clean Water Technology',
            'Clothing & Apparel',
            'Coaching',
            'Construction',
            'Consulting',
            'Consumer Electronics',
            'Copywriting',
            'Cybersecurity',
            'Dentistry',
            'Desserts',
            'Digital Marketing',
            'Digital Publishing',
            'E-commerce',
            'Education',
            'Edutainment',
            'Elderly Care',
            'Electric Vehicles',
            'Entertainment',
            'Environmental Services',
            'Event Planning',
            'Eye Wear',
            'Fashion and Apparel',
            'Finance and Banking',
            'Financial Technology (FinTech)',
            'Fitness and Wellness',
            'Food and Beverage',
            'Footwear',
            'Gaming',
            'Health and Beauty',
            'Healthcare',
            'Home Automation',
            'Home Improvement',
            'Home Appliances',
            'Homewares',
            'Hospitality',
            'Human Resources',
            'Interior Design',
            'Internet of Things (IoT)',
            'Landscaping',
            'Language Learning',
            'Legal Services',
            'Logistics and Supply Chain',
            'Manufacturing',
            'Marketing and Advertising',
            'Medical Devices',
            'Media Production',
            'Nanotechnology',
            'Nonprofit and Social Services',
            'Online Learning',
            'Outdoor Recreation',
            'Personal Development',
            'Personal Finance',
            'Pet Care',
            'Pharmaceuticals',
            'Photography',
            'Professional Services',
            'Real Estate',
            'Renewable Energy',
            'Robotics',
            'Social Impact',
            'Social Media',
            'Software Development',
            'Sports and Fitness',
            'Subscription Services',
            'Sustainable Agriculture',
            'Sustainable Fashion',
            'Technology Services',
            'Telecommunications',
            'Telemedicine',
            'Travel and Tourism',
            'Virtual Reality',
            'Video Production',
            'Wearable Technology',
            'Web Development',
        ];

        $request->validate([
            'full_name' => 'string|required',
            'bio' => 'string|required',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email'),
            ],
            'phone_number' => [
                'required',
                'string',
                Rule::unique('users', 'phone_number'),
            ],
            'country' => 'string|required',
            'url' => 'string|nullable',
            'industry' => 'string|required',
        ]);

        if (!in_array(request()->industry, $options)) {
            return response()->json(['message' => 'Invalid industry'], 400);
        }

        $sm = User::create([
            'full_name' => $request->full_name,
            'bio' => $request->bio,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'url' => $request->url,
            'industry' => $request->industry,
            'country' => $request->country,
            'verified' => 1,
            'special_member' => 1,
            // 'active' => 1,
        ]);
        return response($sm->id, 200);
    }

    public function show($id)
    {
        $user = User::with('links')->find($id);
    
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
    
        // Add the links directly into the user object
        $user->links = $user->links->map(function($link) {
            return [
                'id' => $link->id,
                'title' => $link->title,
                'url' => $link->url,
            ];
        });
    
        return response()->json($user, 200);
    }    

    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $fields['email'])->first();
        if (!$user || !$user->password || !Hash::check($fields['password'], $user->password)) {
            return response([
                'message' => 'Incorrect Email or Password.'
            ], 401);
        }

        $token = $user->createToken('App Token')->plainTextToken;

        $response = [
            'token' => $token
        ];

        return response($response, 201);
    }

    public function setPassword(Request $request)
    {
        $request->validate([
            'verification_token' => 'required',
            'password' => 'required|confirmed',
        ]);

        $entry = VerificationEntry::where('verification_token', request()->verification_token)->whereNull('expires_at')->where('is_expired', false)->first();

        if (!$entry) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        $user = User::where('email', $entry->email)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $entry->is_expired = true;
        $entry->save();

        if (empty($user->password)) {
            $user->password = Hash::make($request->password);
            $user->save();
            return response()->json(['message' => 'Success'], 201);
        } else {
            return response()->json(['message' => 'User already has a password'], 422);
        }
    }

    public function forgotPasswordRequest()
    {
        $validate = Validator::make(request()->all(), [
            'email' => 'required',
        ]);
        if ($validate->fails()) {
            return response()->json(['message' => $validate->messages()->first()], 400);
        }

        $user = User::where('email', request()->email)->where('special_member', false)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $entry = VerificationEntry::where('is_expired', false)->where('email', request()->email)->first();
        if (!$entry) {
            $entry = new VerificationEntry;
            $entry->email = request()->email;
        }

        $entry->verification_token = 'ver_tok_' . GeneralHelpers::generate_random_string(128);
        $entry->expires_at = null;
        $entry->save();

        $href = GeneralHelpers::WEB_APP_URL . '/reset-password?t=' . $entry->verification_token;

        $data = [
            'href' => $href,
            'name' => $user->full_name,
        ];
        $emails = [$entry->email];

        Mail::send('forgotpassword', $data, function ($message) use ($emails) {
            $message->to($emails)->subject('Founders Cube - Reset Your Password');
            $message->from('noreply@mail.thefounderscube.com', 'Founders Cube');
        });

        return response()->json(['message' => 'Success'], 200);
    }

    public function resetPassword()
    {
        $validate = Validator::make(request()->all(), [
            'verification_token' => 'required',
            'password' => 'required|confirmed',
        ]);
        if ($validate->fails()) {
            return response()->json(['message' => $validate->messages()->first()], 400);
        }

        $entry = VerificationEntry::where('verification_token', request()->verification_token)->whereNull('expires_at')->where('is_expired', false)->first();

        if (!$entry) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        $user = User::where('email', $entry->email)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $entry->is_expired = true;
        $entry->save();

        $user->password = Hash::make(request()->password);
        $user->save();

        return response()->json(['message' => 'Success'], 201);
    }

    public function update(Request $request)
    {
        $options = [
            'Accounting',
            'AI Apps',
            'Architecture',
            'AR/VR in Architecture',
            'Artificial Intelligence',
            'Automotive',
            'Beauty Tech',
            'Biotechnology',
            'Blockchain',
            'Childcare Service',
            'Children Services',
            'Clean Energy',
            'Clean Water Technology',
            'Clothing & Apparel',
            'Coaching',
            'Construction',
            'Consulting',
            'Consumer Electronics',
            'Copywriting',
            'Cybersecurity',
            'Dentistry',
            'Desserts',
            'Digital Marketing',
            'Digital Publishing',
            'E-commerce',
            'Education',
            'Edutainment',
            'Elderly Care',
            'Electric Vehicles',
            'Entertainment',
            'Environmental Services',
            'Event Planning',
            'Eye Wear',
            'Fashion and Apparel',
            'Finance and Banking',
            'Financial Technology (FinTech)',
            'Fitness and Wellness',
            'Food and Beverage',
            'Footwear',
            'Gaming',
            'Health and Beauty',
            'Healthcare',
            'Home Automation',
            'Home Improvement',
            'Home Appliances',
            'Homewares',
            'Hospitality',
            'Human Resources',
            'Interior Design',
            'Internet of Things (IoT)',
            'Landscaping',
            'Language Learning',
            'Legal Services',
            'Logistics and Supply Chain',
            'Manufacturing',
            'Marketing and Advertising',
            'Medical Devices',
            'Media Production',
            'Nanotechnology',
            'Nonprofit and Social Services',
            'Online Learning',
            'Outdoor Recreation',
            'Personal Development',
            'Personal Finance',
            'Pet Care',
            'Pharmaceuticals',
            'Photography',
            'Professional Services',
            'Real Estate',
            'Renewable Energy',
            'Robotics',
            'Social Impact',
            'Social Media',
            'Software Development',
            'Sports and Fitness',
            'Subscription Services',
            'Sustainable Agriculture',
            'Sustainable Fashion',
            'Technology Services',
            'Telecommunications',
            'Telemedicine',
            'Travel and Tourism',
            'Virtual Reality',
            'Video Production',
            'Wearable Technology',
            'Web Development',
        ];

        $user = User::find($request->user()->id);

        $validatedData = $request->validate([
            'full_name' => 'required|string',
            'bio' => 'required|string|min:300',
            'phone_number' => 'required|numeric',
            'url' => 'nullable|string',
            'country' => 'required|string',
            'industry' => 'required|string',
            'company' => 'nullable|string',
            'links' => 'nullable|array',
            'links.*.title' => 'required|string',
            'links.*.url' => 'required|url',
        ]);

        if (!in_array($validatedData['industry'], $options)) {
            return response()->json(['message' => 'Invalid industry'], 400);
        }

        $user->fill($validatedData);
        $user->save();

        foreach ($validatedData['links'] as $linkData) {
            $linkExists = $user->links()->where('url', $linkData['url'])->exists();

            if (!$linkExists) {
                $user->links()->create([
                    'title' => $linkData['title'],
                    'url' => $linkData['url'],
                ]);
            }
        }

        return response("User details and links updated successfully", 200);
    }

    public function profilePicture(Request $request)
    {
        $validate = Validator::make(request()->all(), [
            'profile_picture' => 'required|mimes:jpeg,png,jpg',
        ]);
        if ($validate->fails()) {
            return response()->json(['message' => $validate->messages()->first()], 400);
        }

        $user = User::findOrFail($request->user()->id);
        // Process profile picture

        // $resizedImagePath = $this->processProfilePicture($request->file('profile_picture'));

        $imagePath = GeneralHelpers::uploadProfilePic(request()->profile_picture);
        if (!$imagePath) {
            return response()->json(['message' => 'File Error'], 400);
        }

        $user->profile_picture = $imagePath;
        $user->save();

        return response()->json(['message' => 'Success'], 200);
    }
    public function specialMemberProfilePicture(Request $request, $id)
    {
        $validate = Validator::make(request()->all(), [
            'profile_picture' => 'required|mimes:jpeg,png,jpg',
        ]);
        if ($validate->fails()) {
            return response()->json(['message' => $validate->messages()->first()], 400);
        }

        $user = User::findOrFail($id);
        // Process profile picture

        // $resizedImagePath = $this->processProfilePicture($request->file('profile_picture'));

        $imagePath = GeneralHelpers::uploadProfilePic(request()->profile_picture);
        if (!$imagePath) {
            return response()->json(['message' => 'File Error'], 400);
        }

        $user->profile_picture = $imagePath;
        $user->save();

        return response()->json(['message' => 'Success'], 200);
    }

    // Helper function to process profile picture
    private function processProfilePicture($file)
    {
        // Ensure file is valid and readable
        if (!$file->isValid() || !$file->isReadable()) {
            abort(400, 'Invalid or unreadable profile picture file.');
        }

        // Store the image
        $imagePath = $file->store('profile_pictures', 'public');

        // Resize the image
        $this->resizeImage($imagePath);

        return $imagePath;
    }

    // Helper function to resize the image
    private function resizeImage($imagePath)
    {
        $image = Image::make(Storage::disk('public')->path($imagePath));
        $image->fit(200, 200);
        $image->save();
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|confirmed',
        ]);

        $user = $request->user();
        $user->password = Hash::make($request->password);
        $user->save();

        return response("Password reset successfully", 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return [
            'Message' => 'Logged Out'
        ];
    }


    // ADMIN AREA
    public function verify($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response('Member not found', 404);
        }

        $user->verified = true;
        $user->save();

        return response('Verified', 200);
    }

    public function unverify($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response('Member not found', 404);
        }

        $user->verified = false;
        $user->save();

        return response('Unverified', 200);
    }

    public function kickout($id)
    {
        $user = User::findOrFail($id);
        if (!$user) {
            return response('Member not found', 404);
        }

        $data = [
            'name' => $user->full_name
        ];
        $emails = [$user->email];
        Mail::send('kickoutemail', $data, function ($message) use ($emails) {
            $message->to($emails)->subject('The Founders Cube - You have been kicked out');
            $message->from('noreply@mail.thefounderscube.com', 'The Founders Cube');
        });
        $user->transactions()->forceDelete();
        $userApplication = $user->application;
        $user->forceDelete();
        $userApplication->forceDelete();

        return response('Member deleted', 200);
    }

    public function  updateSpecialMember(Request $request, $id)
    {

        $options = [
            'Accounting',
            'AI Apps',
            'Architecture',
            'AR/VR in Architecture',
            'Artificial Intelligence',
            'Automotive',
            'Beauty Tech',
            'Biotechnology',
            'Blockchain',
            'Childcare Service',
            'Children Services',
            'Clean Energy',
            'Clean Water Technology',
            'Clothing & Apparel',
            'Coaching',
            'Construction',
            'Consulting',
            'Consumer Electronics',
            'Copywriting',
            'Cybersecurity',
            'Dentistry',
            'Desserts',
            'Digital Marketing',
            'Digital Publishing',
            'E-commerce',
            'Education',
            'Edutainment',
            'Elderly Care',
            'Electric Vehicles',
            'Entertainment',
            'Environmental Services',
            'Event Planning',
            'Eye Wear',
            'Fashion and Apparel',
            'Finance and Banking',
            'Financial Technology (FinTech)',
            'Fitness and Wellness',
            'Food and Beverage',
            'Footwear',
            'Gaming',
            'Health and Beauty',
            'Healthcare',
            'Home Automation',
            'Home Improvement',
            'Home Appliances',
            'Homewares',
            'Hospitality',
            'Human Resources',
            'Interior Design',
            'Internet of Things (IoT)',
            'Landscaping',
            'Language Learning',
            'Legal Services',
            'Logistics and Supply Chain',
            'Manufacturing',
            'Marketing and Advertising',
            'Medical Devices',
            'Media Production',
            'Nanotechnology',
            'Nonprofit and Social Services',
            'Online Learning',
            'Outdoor Recreation',
            'Personal Development',
            'Personal Finance',
            'Pet Care',
            'Pharmaceuticals',
            'Photography',
            'Professional Services',
            'Real Estate',
            'Renewable Energy',
            'Robotics',
            'Social Impact',
            'Social Media',
            'Software Development',
            'Sports and Fitness',
            'Subscription Services',
            'Sustainable Agriculture',
            'Sustainable Fashion',
            'Technology Services',
            'Telecommunications',
            'Telemedicine',
            'Travel and Tourism',
            'Virtual Reality',
            'Video Production',
            'Wearable Technology',
            'Web Development',
        ];
        $user = User::find($request->user()->id);

        // Validate the request data
        $request->validate([
            'full_name' => 'required|string',
            'email' => 'required|string',
            'country' => 'required|string',
            'bio' => 'required|string|min:300',
            'phone_number' => 'required|numeric',
            'url' => 'nullable|string',
            'industry' => 'required|string',
        ]);

        if (!in_array(request()->industry, $options)) {
            return response()->json(['message' => 'Invalid industry'], 400);
        }

        $user = User::find($id);
        if (!$user) {
            return response('User not found', 404);
        }

        $user->full_name = $request->full_name;
        $user->bio = $request->bio;
        $user->phone_number = $request->phone_number;
        $user->url = $request->url;
        $user->industry = $request->industry;
        $user->email = $request->email;
        $user->save();

        return response('Updated', 200);
    }

    public function deleteSpecialMember($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response('User not found', 404);
        }
        $user->forceDelete();
        return response('User Deleted', 200);
    }
    
    public function makeEditor($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json(['message' => 'Success'], 200);
    }
    
    public function removeEditor($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json(['message' => 'Success'], 200);
    }
    
    
}
