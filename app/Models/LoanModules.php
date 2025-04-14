<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanModules extends Model
{
    protected $table = 'loan_modules';

    protected $primaryKey = 'id';
    
    protected $fillable = [
        'admin_id',
        'name',
        'description',
        'logo',
        'slug',
        'status',
        'created_at',
        'updated_at'
    ];

    public function loanApplications()
    {
        return $this->hasMany(LoanApplications::class, 'module_id');
    }


    public function products()
    {
        return $this->hasMany(Products::class, 'module_id');
    }
}
