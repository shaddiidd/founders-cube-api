<?php

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    use VerifiesEmails;

    public function verify(Request $request)
    {
        $user = User::find($request->route('id'));

        if ($user && hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            $user->markEmailAsVerified();

            return redirect(config('app.client_url') . '/' . RouteServiceProvider::HOME);
        }

        return response(['message' => 'Invalid verification link'], 403);
    }
}
