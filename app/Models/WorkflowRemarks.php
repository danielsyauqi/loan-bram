<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowRemarks extends Model
{
    protected $table = 'workflow_remarks';

    protected $primaryKey = 'id';

    protected $fillable = [
        'application_id',
        'remarks',
        'status',
        'user',
        'created_at',
        'updated_at',
        'user_id'
    ];

    public function application()
    {
        return $this->belongsTo(LoanApplications::class);
    }
    
    
}
