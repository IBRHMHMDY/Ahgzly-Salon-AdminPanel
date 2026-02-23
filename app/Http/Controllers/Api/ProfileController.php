<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * جلب بيانات المستخدم الحالي
     */
    public function show(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user(), // يرجع بيانات المستخدم المسجل دخوله
        ]);
    }

    /**
     * تحديث بيانات الملف الشخصي
     */
    public function update(Request $request)
    {
        $user = $request->user();

        // 1. التحقق من البيانات المرسلة
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'phone'    => ['required', 'string', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'], // كلمة المرور اختيارية
        ]);

        // 2. تحديث البيانات الأساسية
        $user->name = $validated['name'];
        $user->phone = $validated['phone'];

        // 3. تحديث كلمة المرور فقط في حال إرسالها
        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث ملفك الشخصي بنجاح',
            'data' => $user
        ]);
    }
}