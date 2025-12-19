<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class RedisTestController extends Controller
{
    public function test()
    {
        $users = Cache::remember('users', 3600, function () {
            return User::all();
        });

        return response()->json([
            'users' => $users,
        ]);
    }
}
