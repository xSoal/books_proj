<?php

namespace App\Models;

//use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use PhpMyAdmin\Message;

class User extends Authenticatable //implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'active',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function isAdmin()
    {
        return $this->is_admin;
    }

    public function post(){
        return $this->hasOne(Post::class,'id','post_id');
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class, 'user_one_id')
                    ->orWhere('user_two_id', $this->id);
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }
    
    public function newMessagesCount()
    {
        if((int)$this->role === 3){
            return false;
        }

        $chats = Conversation::where('user_one_id', $this->id)
            ->orWhere('user_two_id', $this->id)->get();
        $messagesUnread = [];
        
        foreach ($chats as $key => $chat) {
            $chat_with_id = (int)$chat->userOne->id;
            if((int)$this->role === 1){
                $chat_with_id = (int)$chat->userTwo->id;
            }

            foreach ($chat->messages as $keyMessage => $message) {
                if((int)$message->sender_id === $chat_with_id && (int)$message->is_read === 0)
                $messagesUnread[] = $message;
            }
        }
        return  count($messagesUnread);
    }
    
    

}
