<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;  // Ensure User model is imported
use Illuminate\Support\Facades\Auth;  // Make sure Auth is imported
use Illuminate\View\View;  // Import the View class for type hinting

class UserController extends Controller
{
    //
    public function index():view{
        
        $users = User::where('id', '!=', Auth::id())->withCount(['unreadMessages'])->get();
        return view('dashboard', compact('users'));
    }
    public function userChat(int $userId):view
    {
        return view('user-chat',compact('userId'));
    }
}
