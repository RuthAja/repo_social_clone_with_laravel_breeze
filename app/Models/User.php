<?php

namespace App\Models;

use App\Traits\Following;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Following;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
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
    ];

    public function gravatar($size = 100)
    {
        $default = 'mm';
        return "https://www.gravatar.com/avatar/" . md5(strtolower(trim($this->email))) . "?d=" . urlencode($default) . "&s=" . $size;
    }

    public function statuses()
    {
        return $this->hasMany(Status::class);
    }

    public function makeStatus($string)
    {
        $this->statuses()->create([
            'body' => $string,
            'identifier' => Str::slug($this->id . Str::random(32)),
        ]);
    }

    public function timeline()
    {
        $following = $this->follows->pluck('id');
        // NOTE tambah with atau eagerload fitur laravel untuk meringankan loading dadlam ndatabase
        // return Status::with('user')
        //     ->whereIn('user_id', $following)
        //     ->orWhere('user_id', $this->id)
        //     ->latest()
        //     ->get();

        // NOTE kode dibawah ini sudah mengugnakan eagerload di Status.php di protected $with
        return Status::whereIn('user_id', $following)
            ->orWhere('user_id', $this->id)
            ->latest()
            ->get();
    }

    // NOTE sudah dipindahkan di folder Traits Following.php
    // public function follows()
    // {
    //     return $this->belongsToMany(User::class, 'follows', 'user_id', 'following_user_id')->withTimestamps();
    // }

    // public function followers()
    // {
    //     return $this->belongsToMany(User::class, 'follows', 'following_user_id', 'user_id')->withTimestamps();
    // }

    // public function follow(User $user)
    // {
    //     return $this->follows()->save($user);
    // }

    // public function unfollow(User $user)
    // {
    //     return $this->follows()->detach($user);
    // }

    // public function hasFollow(User $user)
    // {
    //     return $this->follows()->where('following_user_id', $user->id)->exists();
    // }
}
