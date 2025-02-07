<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    //
    protected $fillable =[
        'sender_id',
        'receiver_id',
        'message',
        'file_name',
        'file_original_name',
        'folder_path',
        'is_read',
    ];
    /**
     * Function sender
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id','id');
    }
    /**
     * Function sender
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id','id');
    }
}
