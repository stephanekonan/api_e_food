<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Laravel\Firebase\Facades\Firebase;
class NotificationController extends Controller
{

    protected $database;

    public function __construct()
    {
        $this->database = Firebase::database();
    }
    
    public function registerToken(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|string',
            'role' => 'required|string',
            'fcm_token' => 'required|string',
        ]);

        $this->database->getReference("users/{$validated['user_id']}/fcm_token")
                ->set($validated['fcm_token']);

        return response()->json(['message' => 'Token enregistré']);
    }
    
}
