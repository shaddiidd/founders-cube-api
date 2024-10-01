<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Helpers\GeneralHelpers;
use App\Models\Package;
use App\Models\User;
use Validator;
use Mail;

class TransactionController extends Controller
{
    // User submits a new transaction
    public function store(Request $request, $id)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg',
            'type' => 'required|string|in:cliq,bank',
        ]);

        $imagePath = GeneralHelpers::uploadFile(request()->image);

        $package = Package::find($id);
        if (!$package) {
            return response()->json(['message' => 'Package Not Found'], 404);
        }

        $new = [
            'user_id' => $request->user()->id,
            'type' => $request->type,
            'package_id' => $id,
            'image' => $imagePath,
        ];

        $transaction = Transaction::create($new);
        $user = $transaction->user;

        $data = [
            'name' => $user->full_name,
            'id' => $transaction->id,
            'package_name' => $package->name,
            'price' => $package->price
        ];
        $emails = [$user->email];

        Mail::send('transactionackemail', $data, function($message) use($emails) {
            $message->to($emails)->subject('The Founders Cube - Transaction Acknowledged');
            $message->from('noreply@mail.thefounderscube.com', 'The Founders Cube');
        });
        // Mail::send('newtransactionrecieved', $data, function($message) {
        //     $message->to("abdullah.shadid49@gmail.com")->subject('The Founders Cube - New User Tranaction');
        //     $message->from('noreply@mail.thefounderscube.com', 'The Founders Cube');
        // });

        return response()->json(['message' => 'Transaction created successfully'], 201);
    }

    // ADMIN
    public function index()
    {
        $transactions = Transaction::with(['user:id,full_name'])
            ->select('id', 'status', 'user_id', 'type', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        $transactions->each->append('image_url');

        // If you want to customize the response structure
        $formattedTransactions = $transactions->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'status' => $transaction->status,
                'type' => $transaction->type,
                'user' => [
                    'id' => $transaction->user->id,
                    'full_name' => $transaction->user->full_name,
                ],
            ];
        });

        return response()->json($formattedTransactions, 200);
    }
    public function homeIndex()
    {
        $transactions = Transaction::with(['user:id,full_name'])
            ->select('id', 'status', 'user_id', 'type')
            ->orderBy('created_at', 'desc')
            ->take(2)
            ->get();

        $transactions->each->append('image_url');

        // If you want to customize the response structure
        $formattedTransactions = $transactions->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'status' => $transaction->status,
                'type' => $transaction->type,
                'user' => [
                    'id' => $transaction->user->id,
                    'full_name' => $transaction->user->full_name,
                ],
            ];
        });

        return response()->json($formattedTransactions, 200);
    }



    public function show($id)
    {
        $transactions = Transaction::with(['user:id,full_name,email,phone_number,application_id,profile_picture', 'package:id,name,price'])
            ->select('id', 'image', 'status', 'user_id', 'package_id', 'type', 'created_at')
            ->find($id);
        if (!$transactions) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json($transactions, 200);
    }

    public function confirm($id)
    {
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        $transaction->status = 'Confirmed';
        $transaction->save();

        $user = $transaction->user;

        if ($user->subscription_ends_at) {
            $subscription_ends_at = \Carbon\Carbon::parse($user->subscription_ends_at);
        } else {
            $subscription_ends_at = \Carbon\Carbon::now();
        }

        $subscription_ends_at->addMonths($transaction->package->months);

        $user->active = true;
        $user->is_subscription_active = true;
        $user->subscription_ends_at = $subscription_ends_at->toDateTimeString();
        $user->save();

        if ($user->user_application && $user->user_application->referred_by) {
            if (Transaction::where('user_id', $user->id)->where('status', 'Confirmed')->count() == 1) {
                $ref = User::where('referral_code', $user->user_application->referred_by)->first();
                if ($ref) {
                    $subscription_ends_at->addMonths(2);
                    $user->subscription_ends_at = $subscription_ends_at->toDateTimeString();
                    $user->save();

                    if ($ref->subscription_ends_at) {
                        $subscription_ends_at = \Carbon\Carbon::parse($ref->subscription_ends_at);
                    } else {
                        $subscription_ends_at = \Carbon\Carbon::now();
                    }
                    $subscription_ends_at->addMonths(2);

                    $ref->active = true;
                    $ref->is_subscription_active = true;
                    $ref->subscription_ends_at = $subscription_ends_at->toDateTimeString();
                    $ref->save();
                }
            }
        }

        $data = [
            'name' => $user->full_name,
            'price' => '' . $transaction->package->price,
            'package_name' => $transaction->package->name,
        ];
        $emails = [$user->email];

        Mail::send('transactionacceptedemail', $data, function($message) use($emails) {
            $message->to($emails)->subject('The Founders Cube - Payment Confirmed');
            $message->from('noreply@mail.thefounderscube.com', 'The Founders Cube');
        });

        return response()->json(['message' => 'Transaction confirmed successfully'], 200);
    }
    public function decline($id)
    {
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        $transaction->status = 'Declined';
        $transaction->save();

        $user = $transaction->user;

        $data = [
            'name' => $user->full_name,
        ];
        $emails = [$user->email];

        Mail::send('transactiondeclinedemail', $data, function($message) use($emails) {
            $message->to($emails)->subject('The Founders Cube - Payment Declined');
            $message->from('noreply@mail.thefounderscube.com', 'The Founders Cube');
        });

        return response()->json(['message' => 'Transaction declined successfully'], 200);
    }

    public function deactivateAccountForPayment($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        $user->active = false;
        $user->is_subscription_active = false;
        $user->save();

        return response()->json(['message' => 'Success'], 200);
    }

    public function freeMonths($id)
    {
        $validate = Validator::make(request()->all(), [
            'number_of_months' => 'required|integer',
        ]);
        if ($validate->fails()) {
            return response()->json(['message' => $validate->messages()->first()], 400);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        if ($user->subscription_ends_at) {
            $subscription_ends_at = \Carbon\Carbon::parse($user->subscription_ends_at);
        } else {
            $subscription_ends_at = \Carbon\Carbon::now();
        }

        $subscription_ends_at->addMonths(request()->number_of_months);

        $user->active = true;
        $user->is_subscription_active = true;
        $user->subscription_ends_at = $subscription_ends_at->toDateTimeString();
        $user->save();

        return response()->json(['message' => 'Success'], 200);
    }
}
