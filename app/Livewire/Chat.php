<?php

namespace App\Livewire;

use App\Events\MessageSentEvent;
use App\Events\UnreadMessage;
use App\Events\UserTyping;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class Chat extends Component
{
    use WithFileUploads;

    public $user;
    public $senderId;
    public $receiverId;
    public $message;
    public $messages = [];
    public $file;

    public function mount($userId)
    {
        Log::info("Chat Component Mounted: User ID = {$userId}");

        $this->dispatch('messages-updated');

        $this->senderId   = Auth::id();
        $this->receiverId = $userId;

        # Get User
        $this->user = $this->getUser($userId);
        Log::info("User Retrieved:", ['user' => $this->user]);

        # Get Messages
        $this->messages = $this->getMessages();
        Log::info("Messages Retrieved:", ['messages' => $this->messages]);

        # Read Messages
        $this->markMessagesAsRead();
    }

    public function render()
    {
        Log::info("Rendering Chat Component for Sender ID: {$this->senderId} and Receiver ID: {$this->receiverId}");

        # Read Messages
        $this->markMessagesAsRead();

        return view('livewire.chat');
    }

    public function getUser($userId)
    {
        return User::find($userId);
    }

    public function sendMessage()
    {
        if (!$this->message && !$this->file) {
            Log::warning("No message or file found to send.");
            return;
        }

        Log::info("Sending Message from {$this->senderId} to {$this->receiverId}");

        $sentMessage = $this->saveMessage()->load('sender:id,name', 'receiver:id,name');

        # Append message manually for sender
        $this->messages[] = $sentMessage;
        Log::info("Message Saved:", ['message' => $sentMessage]);

        # Broadcast Sent Message Event
        broadcast(new MessageSentEvent($sentMessage))->toOthers();
        Log::info("Broadcasted MessageSentEvent");

        # Calculate unread messages for receiver
        $unreadCount = $this->getUnreadMessagesCount();
        Log::info("Unread Messages for Receiver {$this->receiverId}: {$unreadCount}");

        # Broadcast unread message count
        broadcast(new UnreadMessage($this->receiverId, $this->senderId, $unreadCount))->toOthers();
        Log::info("Broadcasted UnreadMessage Event");

        $this->message = null;
        $this->file    = null;

        # Emit scroll event
        $this->dispatch('messages-updated');
    }

    #[On('echo-private:chat-channel.{senderId},MessageSentEvent')]
    public function listenMessage($event)
    {
        Log::info("Received MessageSentEvent via WebSockets", ['event' => $event]);

        $newMessage = Message::find($event['message']['id'])->load('sender:id,name', 'receiver:id,name');

        $this->messages[] = $newMessage;
    }

    public function saveMessage()
    {
        Log::info("Saving Message");

        $filePath         = null;
        $fileOriginalName = null;
        $fileName         = null;
        $fileType         = null;

        if ($this->file) {
            Log::info("Processing File Upload");

            $fileOriginalName = $this->file->getClientOriginalName();
            $fileName         = $this->file->hashName();
            $filePath         = $this->file->store('chat_files', 'public');
            $fileType         = $this->file->getMimeType();

            Log::info("File Uploaded", [
                'file_original_name' => $fileOriginalName,
                'file_name'          => $fileName,
                'file_path'          => $filePath,
                'file_type'          => $fileType,
            ]);
        }

        return Message::create([
            'message'           => $this->message,
            'sender_id'         => $this->senderId,
            'receiver_id'       => $this->receiverId,
            'file_name'         => $fileName,
            'file_original_name'=> $fileOriginalName,
            'file_path'         => $filePath,
            'file_type'         => $fileType,
        ]);
    }

    public function getMessages()
    {
        Log::info("Fetching Messages for Chat");

        return Message::with('sender:id,name', 'receiver:id,name')
            ->where(function ($query) {
                $query->where('sender_id', $this->senderId)
                    ->where('receiver_id', $this->receiverId);
            })
            ->orWhere(function ($query) {
                $query->where('sender_id', $this->receiverId)
                    ->where('receiver_id', $this->senderId);
            })
            ->get();
    }

    public function userTyping()
    {
        Log::info("User Typing Event from {$this->senderId} to {$this->receiverId}");

        broadcast(new UserTyping($this->senderId, $this->receiverId))->toOthers();
    }

    public function getUnreadMessagesCount()
    {
        $count = Message::where('receiver_id', $this->receiverId)
            ->where('is_read', false)
            ->count();

        Log::info("Unread Messages Count for Receiver {$this->receiverId}: {$count}");

        return $count;
    }

    public function markMessagesAsRead()
    {
        Log::info("Marking Messages as Read for Sender {$this->senderId} and Receiver {$this->receiverId}");

        Message::where('receiver_id', $this->senderId)
            ->where('sender_id', $this->receiverId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        broadcast(new UnreadMessage($this->senderId, $this->receiverId, 0))->toOthers();
    }

    public function sendFileMessage()
    {
        if ($this->file) {
            Log::info("File Selected for Sending");
            $this->sendMessage();
        }
    }
}
