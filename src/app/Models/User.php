<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'password',
        'role',
        'address',
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
        'password' => 'hashed',
    ];

    public function getAuthIdentifierName()
    {
        return 'user_id';
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function children()
    {
        return $this->hasMany(Child::class);
    }

    public function isProfileComplete(): bool
    {
        if(!$this->user_id || !$this->name || !$this->password){
            return false;
        }
        
        $child = $this->children()->first();
        if(!$child) {
            return false;
        }
        if(!$child->child_name || !$child->birthday || !$child->gender){
            return false;
        }

        if($this->contacts->count() < 2){
            return false;
        }
        foreach ($this->contacts as $contact){
            if(!$contact->contact_name || !$contact->relationship || !$contact->phone_number ){
                return false;
            }
        }
        return true;
    }
}
