<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Redemptions extends Model
{
    protected $table = 'redemptions';

    protected $primaryKey = 'id';


    protected $fillable = [
        'customer_id',
        'bank_coop',
        'expiry_date',
        'redemption_amount',
        'monthly_installment',
        'remark',
        'action',
        'created_at',
        'updated_at'
    ];
    
    public function customer()
    {
        return $this->belongsTo(User::class);
    }
    
    
}
