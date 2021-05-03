<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'loan_id','term','payment','principal','interest','balance'
    ];


    /**
     * relationships
     */
    public function loan() {
        return $this->belongsTo(Loan::class);
    }

    /**
     * methods
     */

    

}