<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;  // Import the User model

class Chat extends Component
{
    public $user;

    // Mount method to initialize the user based on userId
    public function mount($userId)
    {
        // Fetch the user with the provided userId
        $this->user = $this->getUser($userId);
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
}
