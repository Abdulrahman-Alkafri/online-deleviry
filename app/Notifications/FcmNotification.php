<?php  

namespace App\Notifications;  

use Illuminate\Bus\Queueable;  
use Illuminate\Contracts\Queue\ShouldQueue;  
use Illuminate\Notifications\Notification;  

class FcmNotification extends Notification implements ShouldQueue  
{  
    use Queueable;  

    public $title;  
    public $body;  
    public $data;  

    public function __construct($title, $body, array $data = [])  
    {  
        $this->title = $title;  
        $this->body = $body;  
        $this->data = $data;  
    }  

    public function via($notifiable)  
    {  
        return ['fcm'];  
    }  

    public function toFcm($notifiable)  
    {  
        return [  
            'title' => $this->title,  
            'body' => $this->body,  
            'data' => $this->data,  
        ];  
    }  
}