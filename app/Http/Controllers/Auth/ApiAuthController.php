<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ApiAuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
        if ($validator->fails()) {
            return response([
                'success' => false,
                'result' => [],
                'errors' => $validator->errors()->all(),
                'message' => "Invalid inputs",
                'status' => 422,
            ], 422);
        }
        $request['password'] = Hash::make($request['password']);
        $request['remember_token'] = Str::random(10);
        $user = User::create($request->toArray());
        $token = $user->createToken('Laravel Password Grant Client')->accessToken;
        $response = [
            'success' => true,
            'result' => [],
            'token' => $token,
            'errors' => $validator->errors()->all(),
            'message' => "Register success",
            'status' => 200,
        ];
        return response($response, 200);
    }
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response([
                'success' => false,
                'result' => [],
                'errors' => $validator->errors()->all(),
                'message' => "Invalid inputs",
                'status' => 422,
            ], 422);
        }
        $user = User::where('email', $request->email)->first();
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken('Laravel Password Grant Client')->accessToken;
                $response = [
                    'success' => true,
                    'result' => [],
                    'token' => $token,
                    'errors' => $validator->errors()->all(),
                    'message' => "Login success",
                    'status' => 200,
                ];
                return response($response, 200);
            } else {
                $response = [
                    'success' => false,
                    'result' => [],
                    'token' => null,
                    'errors' => $validator->errors()->all(),
                    'message' => "Password mismatch",
                    'status' => 422,
                ];
                return response($response, 422);
            }
        } else {
            $response = [
                'success' => false,
                'result' => [],
                'token' => null,
                'errors' => $validator->errors()->all(),
                'message' => "User does not exist",
                'status' => 422,
            ];
            return response($response, 422);
        }
    }
    public function logout(Request $request)
    {
        $token = $request->user()->token();
        $token->revoke();
        $response = [
            'success' => false,
            'result' => [],
            'errors' => [],
            'message' => 'You have been successfully logged out',
            'status' => 200,
        ];
        return response($response, 200);
    }
    public function submitForgetPasswordForm(Request $request)
    {
        // $request->validate([
        //     'email' => 'required|email|exists:users',
        // ]);
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users',
        ]);
        if ($validator->fails()) {
            return response([
                'success' => false,
                'result' => [],
                'errors' => $validator->errors()->all(),
                'message' => "Invalid inputs",
                'status' => 422,
            ], 422);
        }
        $token = Str::random(64);
        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);
        Mail::send('email.forgetPassword', ['token' => $token], function ($message) use ($request) {
            $message->to($request->email);
            $message->subject('Reset Password');
        });
        $response = [
            'success' => true,
            'result' => [],
            'errors' => [],
            'message' => 'We have e-mailed your password reset link',
            'status' => 200,
        ];
        return response($response, 200);
    }
    public function submitResetPasswordForm(Request $request)
    {
        // $request->validate([
        //     'email' => 'required|email|exists:users',
        //     'password' => 'required|string|min:6|confirmed',
        //     'password_confirmation' => 'required'
        // ]);
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required'
        ]);
        if ($validator->fails()) {
            return response([
                'success' => false,
                'result' => [],
                'errors' => $validator->errors()->all(),
                'message' => "Invalid inputs",
                'status' => 422,
            ], 422);
        }
        $updatePassword = DB::table('password_resets')
            ->where([
                'email' => $request->email,
                'token' => $request->token
            ])
            ->first();
        if (!$updatePassword) {
            // return back()->withInput()->with('error', 'Invalid token!');
            $response = [
                'success' => true,
                'result' => [],
                'errors' => [],
                'message' => 'Invalid token!',
                'status' => 422,
            ];
            return response($response, 422);
        }
        $user = User::where('email', $request->email)
            ->update(['password' => Hash::make($request->password)]);
        DB::table('password_resets')->where(['email' => $request->email])->delete();

        $response = [
            'success' => true,
            'result' => [],
            'errors' => [],
            'message' => 'Your password has been changed!',
            'status' => 200,
        ];
        return response($response, 200);
    }
    // ------------------------------------------
    public function unauthenticated()
    {
        $response = [
            'success' => false,
            'result' => [],
            'errors' => [],
            'message' => 'Unauthenticated',
            'status' => 403,
        ];
        return response($response, 403);
    }
    public function private_area()
    {
        $response = [
            'success' => true,
            'result' => [],
            'errors' => [],
            'message' => 'Private Area',
            'status' => 200,
        ];
        return response($response, 200);
    }
}
