<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanApplications extends Model
{
    protected $table = 'loan_applications';

    protected $primaryKey = 'id';


    protected $fillable = [
        'customer_id',
        'product_id',
        'agent_id',
        'module_id',
        'sub_agent_id',
        'biro',
        'banca',
        'rates',
        'document_checklist',
        'date_received',
        'date_rejected',
        'date_approved',
        'date_disbursed',
        'date_submitted',
        'tenure_applied',
        'tenure_approved',
        'amount_applied',
        'amount_approved',
        'amount_disbursed',
        'created_at',
        'updated_at',
        'application_id',
        'reference_id',
        'admin_id',
        
    ];

    public function module()
    {
        return $this->belongsTo(LoanModules::class, 'module_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function subAgent()
    {
        return $this->belongsTo(User::class, 'sub_agent_id');
    }


    public function product()
    {
        return $this->belongsTo(Products::class , 'product_id');
    }
    

    public function workflowRemarks()
    {
        return $this->hasOne(WorkflowRemarks::class, 'application_id');
    }
    
    
}
