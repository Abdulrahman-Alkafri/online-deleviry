<?php  

namespace App\Http\Controllers;  

use Illuminate\Http\Request;  
use Illuminate\Support\Facades\Hash;  
use App\Models\User;  
use Illuminate\Support\Facades\Validator;  
use Laravel\Sanctum\HasApiTokens;  
use Twilio\Rest\Client;  
use Illuminate\Validation\ValidationException;  
use Symfony\Component\HttpFoundation\Response;  

class AuthController extends Controller  
{  
    public function register(Request $request)  
    {  
        $validator = Validator::make($request->all(), [  
            'name' => 'required|string|max:50',  
            'phone' => 'required|string|unique:users,phone',  
            'password' => 'required|string|min:8',  
            'image' => 'nullable|image|max:2048',  
            'role' => 'string|required',  
            'location' => 'nullable|string|max:255', // Validation for location  
        ]);  

        if ($validator->fails()) {  
            return response()->json($validator->errors(), 400);  
        }  

        // Handle image upload if provided  
        $imagePath = null;  
        if ($request->hasFile('image')) {  
            $imagePath = $request->file('image')->store('images', 'public');  
        }  

        // Generate a verification code  
        $verificationCode = rand(100000, 999999);  

        // Create user with unverified status  
        try {  
            $user = User::create([  
                'name' => $request->name,  
                'role' => $request->role,  
                'phone' => $request->phone,  
                'password' => Hash::make($request->password),  
                'image' => $imagePath,  
                'is_verified' => false,  
                'verification_code' => $verificationCode,  
                'location' => $request->location, // Add location to user creation  
            ]);  
        } catch (\Exception $e) {  
            return response()->json(['message' => 'Failed to register user. Please try again.'], 500);  
        }  

        // Send the verification code via SMS  
        try {  
            $this->sendSMS($user->phone, "Your verification code is: $verificationCode");  
        } catch (\Exception $e) {  
            return response()->json(['message' => 'Failed to send SMS. Please try again.'], 500);  
        }  

        return response()->json(['message' => 'User registered successfully. Please verify your phone.']);  
    }  

    // Twilio SMS Sending Function  
    protected function sendSMS($to, $message)  
    {  
        $sid = env('TWILIO_SID');  
        $token = env('TWILIO_TOKEN');  
        $from = env('TWILIO_FROM');  

        $twilio = new Client($sid, $token);  
        $twilio->messages->create($to, [  
            'from' => $from,  
            'body' => $message,  
        ]);  
    }  

    public function login(Request $request)  
    {  
        $validator = Validator::make($request->all(), [  
            'phone' => 'required|string',  
            'password' => 'required|string',  
        ]);  

        if ($validator->fails()) {  
            return response()->json($validator->errors(), 400);  
        }  

        $user = User::where('phone', $request->phone)->first();  

        if (!$user || !Hash::check($request->password, $user->password)) {  
            return response()->json(['message' => 'Invalid credentials.'], 401);  
        }  

        // Create access token with a duration  
        $token = $user->createToken('auth_token')->plainTextToken;  

        return response()->json([  
            'token' => $token,  
            'expires_in' => 60, // Token expires in 24 hours  
        ]);  
    }  

    public function logout(Request $request)  
    {  
        try {  
            $request->user()->currentAccessToken()->delete();  
            return response()->json(['message' => 'Logged out successfully.']);  
        } catch (\Exception $e) {  
            return response()->json(['message' => 'Failed to log out. Please try again.'], 500);  
        }  
    }  

    public function resetPassword(Request $request)  
    {  
        $validator = Validator::make($request->all(), [  
            'phone' => 'required|string|size:12',  
            'password' => 'required|string|min:8',  
        ]);  

        if ($validator->fails()) {  
            return response()->json($validator->errors(), 400);  
        }  

        $user = User::where('phone', $request->phone)->first();  

        if (!$user) {  
            return response()->json(['message' => 'User not found.'], 404);  
        }  

        // Update the user's password  
        try {  
            $user->password = Hash::make($request->password);  
            $user->save();  
        } catch (\Exception $e) {  
            return response()->json(['message' => 'Failed to reset password. Please try again.'], 500);  
        }  

        return response()->json(['message' => 'Password updated successfully.']);  
    }  

    public function refreshToken(Request $request)  
    {  
        try {  
            // Delete the old token  
            $request->user()->currentAccessToken()->delete();  

            // Create a new token  
            $token = $request->user()->createToken('auth_token')->plainTextToken;  

            return response()->json([  
                'token' => $token,  
                'expires_in' => 60 * 24, // Token expires in 24 hours  
            ]);  
        } catch (\Exception $e) {  
            return response()->json(['message' => 'Failed to refresh token. Please try again.'], 500);  
        }  
    }

    public function verifyPhone(Request $request)  
    {  
        $validator = Validator::make($request->all(), [  
            'phone' => 'required|string',  
            'verification_code' => 'required|numeric',  
        ]);  

        if ($validator->fails()) {  
            return response()->json($validator->errors(), 400);  
        }  

        $user = User::where('phone', $request->phone)->first();  

        if (!$user) {  
            return response()->json(['message' => 'User not found.'], 404);  
        }  

        if ($user->verification_code == $request->verification_code) {  
            $user->is_verified = true;  
            $user->verification_code = null; // Clear the code after verification  

            try {  
                $user->save();  
            } catch (\Exception $e) {  
                return response()->json(['message' => 'Failed to verify phone. Please try again.'], 500);  
            }  

            return response()->json(['message' => 'Phone verified successfully.']);  
        }  

        return response()->json(['message' => 'Invalid verification code.'], 400);  
    }  

    public function requestResetCode(Request $request)  
    {  
        $validator = Validator::make($request->all(), [  
            'phone' => 'required|string',  
        ]);  

        if ($validator->fails()) {  
            return response()->json($validator->errors(), 400);  
        }  

        $user = User::where('phone', $request->phone)->first();  

        if (!$user) {  
            return response()->json(['message' => 'User not found.'], 404);  
        }  

        // Generate a reset code and set expiration  
        $resetCode = rand(100000, 999999);  
        $user->reset_code = $resetCode;  
        // $user->reset_code_expires_at = now()->addMinutes(10); // Uncomment if implementing expiration  
        try {  
            $user->save();  
            $this->sendSMS($user->phone, "Your password reset code is: $resetCode");  
        } catch (\Exception $e) {  
            return response()->json(['message' => 'Failed to send SMS. Please try again.'], 500);  
        }  

        return response()->json(['message' => 'Reset code sent successfully.']);  
    }  

    public function resetPasswordWithCode(Request $request)  
    {  
        $validator = Validator::make($request->all(), [  
            'phone' => 'required|string',  
            'reset_code' => 'required|numeric',  
            'password' => 'required|string|min:8',  
        ]);  

        if ($validator->fails()) {  
            return response()->json($validator->errors(), 400);  
        }  

        $user = User::where('phone', $request->phone)->first();  

        if (!$user) {  
            return response()->json(['message' => 'User not found.'], 404);  
        }  

        // Validate reset code  
        if ($user->reset_code != $request->reset_code) {  
            return response()->json(['message' => 'Invalid or expired reset code.'], 400);  
        }  

        // Update the user's password  
        try {  
            $user->password = Hash::make($request->password);  
            $user->reset_code = null; // Clear the reset code  
            // $user->reset_code_expires_at = null; // Uncomment if implementing expiration  
            $user->save();  
        } catch (\Exception $e) {  
            return response()->json(['message' => 'Failed to reset password. Please try again.'], 500);  
        }  

        return response()->json(['message' => 'Password reset successfully.']);  
    }  

    public function index(Request $request)  
    {  
        if (!$request->user()->can('isSuperAdmin', User::class)) {  
            return response()->json(['message' => 'Unauthorized.'], 403);  
        }  

        $users = User::paginate(25);  
        return response()->json(['users' => $users], 200);  
    }  

    public function show(User $user)  
    {  
        return response()->json(['user' => $user], 200);  
    }  

    public function update(Request $request, User $user)  
    {  
        $validator = Validator::make($request->all(), [  
            'name' => 'sometimes|required|string|max:50',  
            'phone' => 'sometimes|required|string|unique:users,phone,' . $user->id,  
            'password' => 'sometimes|required|string|min:8',  
            'role' => 'sometimes|required|string',  
            'image' => 'nullable|image|max:2048',  
            'location' => 'nullable|string|max:255', // Add location validation  
        ]);  

        if ($validator->fails()) {  
            return response()->json($validator->errors(), 400);  
        }  

        // Update user details  
        try {  
            if ($request->has('name')) {  
                $user->name = $request->name;  
            }  

            if ($request->has('phone')) {  
                $user->phone = $request->phone;  
            }  

            if ($request->has('password')) {  
                $user->password = Hash::make($request->password);  
            }  

            if ($request->hasFile('image')) {  
                $user->image = $request->file('image')->store('images', 'public');  
            }  

            if ($request->has('location')) {  
                $user->location = $request->location; // Update location  
            }  

            $user->role = $request->role;  

            $user->save();  
        } catch (\Exception $e) {  
            return response()->json(['message' => 'Failed to update user. Please try again.'], 500);  
        }  

        return response()->json(['message' => 'User updated successfully.', 'user' => $user], 200);  
    }  

    public function destroy(Request $request, User $user)  
    {  
        if (!$request->user()->can('isSuperAdmin', User::class)) {  
            return response()->json(['message' => 'Unauthorized.'], 403);  
        }  

        if (!$user) {  
            return response()->json(['message' => 'User not found.'], 404);  
        }  

        try {  
            $user->delete();  
        } catch (\Exception $e) {  
            return response()->json(['message' => 'Failed to delete user. Please try again.'], 500);  
        }  

        return response()->json(['message' => 'User deleted successfully.'], 200);  
    }  
}