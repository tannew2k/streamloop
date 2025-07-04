<?php

use App\Enums\LiveStatusEnum;
use App\Http\Requests\ImportCookiesRequest;
use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// api/webhook/channels/{channel}
Route::prefix('/webhook')->group(function () {
    Route::post('/channels', function (Request $request) {
        $validator = Validator::make($request->all(), [
            'username' => 'required|exists:channels,username',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => 'Channel not found',
            ]);
        }
        $username = $request->json('username');
        $channel = Channel::where('username', $username)->first();
        if (!$channel) {
            return response()->json([
                'status' => 0,
                'message' => 'Channel not found',
            ]);
        }

        /** @var Channel $channel */
        Log::error(json_encode($request->all())); // This is the data that is being sent from the webhook
        $channel->update($request->all());

        return response()->json([
            'status' => 1,
            'message' => 'Channel updated successfully',
        ]);
    });
});

Route::post('/cookies', function (ImportCookiesRequest $request) {
    $cookies = $request->json('cookies');
    $username = $request->json('username');

    // Generate random install_id and device_id
    $randomDeviceId = rand(7250000000000000000, 7351147085025500000);
    $randomInstallId = rand(7250000000000000000, 7351147085025500000);

    // if username exists, update the cookies
    $channel = Channel::where('username', $username)->first();
    if ($channel) {
        $channel->update([
            'cookies' => $cookies,
        ]);
        if ($channel->install_id == null || $channel->device_id == null) {
            $channel->update([
                'install_id' => $randomInstallId,
                'device_id' => $randomDeviceId,
            ]);
            Log::info('Install ID and Device ID updated for '.$username);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Cookies updated successfully',
            'data' => [
                'username' => $username,
            ],
        ]);
    } else {
        // if username does not exist, create a new channel
        Channel::create([
            'username' => $username,
            'cookies' => $cookies,
            'install_id' => $randomInstallId,
            'device_id' => $randomDeviceId,
            'user_id' => 1,
        ]);

        return response()->json([
            'status' => 1,
            'message' => 'Cookies saved successfully',
            'data' => [
                'username' => $username,
            ],
        ]);
    }
});
