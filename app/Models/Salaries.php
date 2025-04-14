<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salaries extends Model
{
    protected $table = 'salaries';

    protected $primaryKey = 'id';
    
    protected $fillable = [
        'customer_id',
        'month',
        'year',
        'income',
        'deduction',
        'attachments',
        'attachment',
        'created_at',
        'updated_at'
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

}
