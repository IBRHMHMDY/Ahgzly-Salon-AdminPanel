<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer; // نستخدم موديل العميل لتفعيل الـ STI
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // 1. تسجيل حساب جديد (Register)
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20|unique:users',
            'password' => 'required|string|min:8',
        ]);

        // نستخدم موديل Customer لضمان حفظ الـ type في قاعدة البيانات (STI)
        $customer = Customer::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            // الـ salon_id سيكون null افتراضياً أو يمكن تعيينه للصالون الافتراضي حسب متطلباتك
            'salon_id' => 1,
        ]);

        // إعطاء دور العميل تلقائياً
        $customer->assignRole('Customer');

        // توليد الـ Token لتطبيق Flutter
        $token = $customer->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Customer registered successfully',
            'user' => $customer,
            'token' => $token,
        ], 201);
    }

    // 2. تسجيل الدخول (Login)
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // نبحث باستخدام موديل Customer لنضمن أن من يسجل دخول من التطبيق هو عميل
        $customer = Customer::where('email', $request->email)->first();

        if (! $customer || ! Hash::check($request->password, $customer->password)) {
            throw ValidationException::withMessages([
                'email' => ['بيانات الدخول غير صحيحة.'],
            ]);
        }

        $token = $customer->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Logged in successfully',
            'user' => $customer,
            'token' => $token,
        ]);
    }

    // 3. عرض الملف الشخصي (Profile)
    public function profile(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    // 4. تسجيل الخروج (Logout)
    public function logout(Request $request)
    {
        // حذف التوكن الحالي
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }
}
