<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    protected $table = 'products';

    protected $primaryKey = 'id';

    protected $fillable = [
        'module_id',
        'slug',
        'name',
        'description',
        'minimum_loan',
        'maximum_loan',
        'rate',
        'tenure',
        'requirements',
        'features',
        'status',
        'popularity',
        'created_at',
        'updated_at'
    ];

    public function module()
    {
        return $this->belongsTo(LoanModules::class, 'module_id');
    }

    public function loanApplications()
    {
        return $this->hasMany(LoanApplications::class , 'product_id');
    }
}
