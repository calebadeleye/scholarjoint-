<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use App\Mail\VerifyEmail;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

       
    // Generate signed verification link
    $verificationUrl = URL::temporarySignedRoute(
        'verify.custom',
        now()->addMinutes(60),
        ['id' => $user->id]
    );

    // Send custom mail
    Mail::to($user->email)->queue(new VerifyEmail($user, $verificationUrl));

        return response()->json([
            'message' => 'Registration successful! Please check your email for a verification link.',
            'user'    => $user,
        ], 201);

    }


    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if (!$user->hasVerifiedEmail()) {
                return response()->json([
                    'message' => 'Your email is not verified. Please check your email for the verification link.',
                    'resend_link' => url('/email/verification-notification')
                ], 403);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'token' => $token
            ]);
        }

        return response()->json(['message' => 'Invalid login credentials'], 401);
    }


    public function resendVerification(Request $request)
    {
        // Validate the email input
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        // Check if already verified
        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'This email is already verified.'
            ], 400);
        }

        // Generate signed verification link
        $verificationUrl = URL::temporarySignedRoute(
            'verify.custom',
            now()->addMinutes(60),
            ['id' => $user->id]
        );

        // Send the verification email
        Mail::to($user->email)->queue(new VerifyEmail($user, $verificationUrl));

        return response()->json([
            'message' => 'Verification link has been sent to your email.'
        ], 200);
    }



    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
