<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'borrower_id','description','amount','terms','interest_rate','total_interest','running_balance','approved_at',
    ];

    protected $with = ['borrower','transactions'];

     /**
     * relationships
     */
    public function borrower() {
        return $this->belongsTo(User::class);
    }

    public function transactions() {
        return $this->hasMany(LoanTransaction::class);
    }

    /**
     *  methods
     */
    public function addTransaction($data) {
        $this->transactions()->create($data);
    }
}
