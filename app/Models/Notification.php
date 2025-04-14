<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notification';
    protected $primaryKey = 'id';

    protected $fillable = [ 'title', 'message', 'created_at', 'read_at', 'updated_at', 'sender_id', 'receiver_id', 'reference_id', 'status'];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function getReadAttribute()
    {
        return $this->read_at !== null;
    }

    public function getUnreadAttribute()
    {
        return $this->read_at === null;
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }


}
