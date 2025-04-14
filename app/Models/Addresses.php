<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Addresses extends Model
{
    protected $table = 'addresses';

    protected $primaryKey = 'id';

    protected $fillable = [
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'zip',
        'country',
        'user_id',
        'employment_id',
        'created_at',
        'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employment()
    {
        return $this->belongsTo(Employments::class);
    }
}
