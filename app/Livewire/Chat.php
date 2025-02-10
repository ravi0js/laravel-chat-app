<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\User;  
use App\Models\Message;  

class Chat extends Component
{
    public $user;
    public $message;
    public $senderId;
    public $receiverId;
    public $messages;
    // Mount method to initialize the user based on userId
    public function mount($userId)
    {
        // Fetch the user with the provided userId
        $this->user = $this->getUser($userId);

        $this->senderId = Auth::User()->id;
        $this->receiverId=$userId;
        
        // fetch massage
        $this->messages = $this->getMessages();
    }

    // Render method that returns the view
    public function render()
    {
        return view('livewire.chat');  // Make sure to pass $user to the view if you want to display it
    }

    /**
     * Get the user by userId
     * 
     * @param int $userId
     * @return \App\Models\User|null
     */
    public function getUser($userId)
    {
        return User::find($userId);  // Find user by ID or return null if not found
    }
     /**
     * Function getMessages
     */
    public function getMessages()
    {
        return Message::with('sender','receiver')
        ->where( function($query){
            $query->where('sender_id',$this->senderId)
            ->where('receiver_id',$this->receiverId);
        })
        ->orWhere(function($query)
        {
            $query->where('sender_id',$this->receiverId)
            ->where('receiver_id',$this->senderId);   
        })->get();
    }
    /**
     * Function sendMessage
     */
    public function sendMessage()
    {
        $this->saveMessage();
        $this->message='';
    }
    /**
     * Function: saveMessage
     */
    public function saveMessage()
    {
        return Message::create([
            'sender_id'=>$this -> senderId,
            'receiver_id'=> $this -> receiverId,
            'message'=> $this -> message,
            // 'file_name',
            // 'file_original_name',
            // 'folder_path',
            'is_read'=>false
        ]);
    }
}

