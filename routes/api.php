<?php

use App\Http\Controllers\Api\DriverController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = \App\Models\User::where('email', $request->email)->first();

    if (! $user || ! \Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'بيانات الدخول غير صحيحة'], 422);
    }

    return response()->json([
        'user'  => $user,
        'token' => $user->createToken('mobile')->plainTextToken,
        'roles' => $user->getRoleNames(),
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user());
    Route::post('/logout', fn (Request $r) => tap($r->user()->currentAccessToken()->delete(), fn () => response()->json(['ok' => true])));

    Route::prefix('driver')->group(function () {
        Route::get('deliveries', [DriverController::class, 'deliveries']);
        Route::post('deliveries/{delivery}/start', [DriverController::class, 'start']);
        Route::post('deliveries/{delivery}/complete', [DriverController::class, 'complete']);
        Route::post('deliveries/{delivery}/fail', [DriverController::class, 'fail']);
        Route::post('location', [DriverController::class, 'updateLocation']);
    });
});
