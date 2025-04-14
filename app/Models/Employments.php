<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employments extends Model
{
    protected $table = 'employments';

    protected $primaryKey = 'id';
    
    protected $fillable = [
        'user_id',
        'job_title',
        'phone_num',
        'bank',
        'pension',
        'company_name',
        'date_joined',
        'account_num',
        'emp_type',
        'created_at',
        'updated_at'
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }   

    public function addresses()
    {
        return $this->hasOne(Addresses::class);
    }
}
