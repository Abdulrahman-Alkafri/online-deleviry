<?php  

namespace App\Http\Controllers;  

use App\Models\User;  
use App\Notifications\FcmNotification;  
use Illuminate\Http\Request;  
use Kreait\Firebase\Factory;  
use Kreait\Firebase\Messaging\CloudMessage;  

class FcmController extends Controller  
{  
    protected $messaging;  

    public function __construct()  
    {  
        $firebase = (new Factory)  
            ->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')));  

        $this->messaging = $firebase->createMessaging();  
    }  

    // Example FcmController method  
public function sendNotification($deviceToken, $title, $body, $data = [])  
{  
    if (empty($deviceToken)) {  
        \Illuminate\Support\Facades\Log::error("Device token is null or empty.");
        return response()->json(['message' => 'Device token is required.'], 400);  
    }  

    $notification = new FcmNotification($title, $body, $data);  

    try {  
        $message = CloudMessage::withTarget('token', $deviceToken)  
            ->withNotification(['title' => $notification->title, 'body' => $notification->body])  
            ->withData($notification->data);  

        return $this->messaging->send($message);  
    } catch (\Exception $e) {  
        \Illuminate\Support\Facades\Log::error('Failed to send notification: ' . $e->getMessage());
        return response()->json(['message' => 'Failed to send notification.'], 500);  
    }  
}
}