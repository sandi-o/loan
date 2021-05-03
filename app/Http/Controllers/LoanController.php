<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\ApiResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Loan;
use App\LoanTransaction;
use Carbon\Carbon;

class LoanController extends Controller
{
    use ApiResponse;

     /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * @OA\Get(
     *      path="/api/loans",
     *      operationId="index",
     *      tags={"Loan"},
     *      summary="Gets all the authenticaed users loan",
     *      description="Returns Json of all loan records by the user",
     *      @OA\Response(
     *          response=200,
     *          description="Record(s) found",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          )
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      security={
     *         {"passport": {}}
     *     }
     * )
     */
    public function index() {
        return $this->success(auth()->user()->loans()->get(),'',200);  
    }

    /**
     * @OA\Post(
     *     path="/api/loans",
     *     tags={"Loan"},
     *     summary="Create a loan record",
     *     description="Post data / loan application by the authenticated user",
     *     operationId="store",   
     *      @OA\Parameter(
     *          name="description",
     *          description="description",
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="amount",
     *          description="amount to borrow",
     *          required = true,
     *          in="query",
     *          @OA\Schema(
     *              type="number"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="terms",
     *          description="in weeks (fixed interest rate to 5% per week)",
     *          required = true,
     *          in="query",
     *          @OA\Schema(
     *              type="number"
     *          )
     *      ),
     *      @OA\Response(
     *         response=201,
     *         description="Record Successfully saved.",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Code Error",
    *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      security={
     *         {"passport": {}}
     *      }   
     * )
     */
    public function store(Request $request)
    {       
        $valid = $this->validateIncomingData($request);
    
        if($valid->fails()) {
            return $this->error($valid->messages(),422);
        } else {
            if($request->amount <= 0) {
                return $this->error('Amount is should be greater than 0.',422);
            }
    
            $attributes['borrower_id'] = auth()->id();
            $attributes['description'] = $request->description;
            $attributes['amount'] = $request->amount;
            $attributes['interest_rate'] = 5;
            $attributes['terms'] = $request->terms;
            $attributes['running_balance'] = $request->amount;

            $loan = Loan::create($attributes);

            if($loan) {
                return $this->success($loan,'Record Successfully created.', 201);
            } else {
                return $this->error('Ops. something went wrong.', 500);
            }            
        }   
    }

    /**
     * @OA\Patch(
     *     path="/api/loans/{loan}",
     *     tags={"Loan"},
     *     summary="Update a loan record",
     *     description="Patch data / loan application by the authenticated user",
     *     operationId="update",
     *       @OA\Parameter(
     *          name="loan",
     *          description="Loan Id",
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),   
     *      @OA\Parameter(
     *          name="description",
     *          description="description",
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="amount",
     *          description="amount to borrow",
     *          required = true,
     *          in="query",
     *          @OA\Schema(
     *              type="number"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="terms",
     *          description="in weeks (fixed interest rate to 5% per week)",
     *          required = true,
     *          in="query",
     *          @OA\Schema(
     *              type="number"
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="Record Successfully updated.",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Code Error",
     *      ),
     *      security={
     *         {"passport": {}}
     *      }   
     * )
     */
    public function update(Request $request,Loan $loan)
    {      
        $valid = $this->validateIncomingData($request);
    
        if($valid->fails()) {
            return $this->error($valid->messages(),422);
        } else {
            if($request->amount <= 0)
                return $this->error('Amount is should be greater than 0.',422);

            if(!empty($loan->approved_at)) 
                return $this->error('Cannot update an approved loan.',400);
    
            $attributes['description'] = $request->description;
            $attributes['amount'] = $request->amount;
            $attributes['terms'] = $request->terms;

            $result = $loan->update($attributes);
            
            if($result) {
                return $this->success($result,'Record successfully updated.', 200);
            } else {
                return $this->error('Ops. something went wrong.', 500);
            }  
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/loans/{loan}/approve",
     *     tags={"Loan"},
     *     summary="Approve a loan record",
     *     description="Approve the pending loan application by the authenticated user",
     *     operationId="approve",
     *       @OA\Parameter(
     *          name="loan",
     *          description="Loan Id",
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),   
     *      @OA\Response(
     *         response=200,
     *         description="Record Successfully updated.",
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Code Error"
     *      ),
     *     @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *     ),
     *     security={
     *         {"passport": {}}
     *     }   
     * )
     */
    public function approve(Loan $loan)
    {
        if(!empty($loan->approved_at)) {
            return $this->error('Loan already approved.',400);
        } else {            
            $result = $loan->update(['approved_at'=>Carbon::now()]);
            
            if($result) {
                return $this->success($result,'Record successfully APPROVED.', 200);
            } else {
                return $this->error('Ops. something went wrong. Please try again.', 500);
            }  
        }
    }

    /**
     * @OA\Post(
     *     path="/api/loans/{loan}/full/{terms?}",
     *     tags={"Loan"},
     *     summary="pays the loan in full base on terms",
     *     description="Pay the loan in full base on terms (default value of terms is 1)",
     *     operationId="fullRepayment",   
     *      @OA\Parameter(
     *          name="loan",
     *          description="Loan Id",
     *          required = true,
     *          in="path",
     *          @OA\Schema(
     *              type="number"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="terms",
     *          description="in weeks (fixed interest rate to 5% per week)",
     *          in="path",
     *          @OA\Schema(
     *              type="number"
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="Fully Paid.",
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Code Error",
     *      ),
     *      security={
     *         {"passport": {}}
     *      }   
     * )
     */
    public function fullRepayment(Loan $loan,$terms = 1)
    {        
        $valid = $this->validateLoan($loan);
        if($valid === true) {
            $balance = $loan->amount;
            $principal = $loan->amount / $terms;
            $totalInterest = 0;

            for($term = 1;$term <= $terms; $term++){
                $interest = $balance * ($loan->interest_rate / 100);
                $payment = $principal + $interest;
                $runningBalance = $balance - $principal;

                $attributes['term'] = $term;
                $attributes['principal'] = $principal;
                $attributes['interest'] = $interest;
                $attributes['payment'] = $payment;
                $attributes['balance'] =  $runningBalance;
                
                $transaction = $loan->addTransaction($attributes);

                $balance = $runningBalance;
                $totalInterest += $interest;
            }

            $result = $loan->update(['running_balance'=> $balance,'total_interest'=>$totalInterest]);

            if($result){
                return $this->success($result,'Record successfully PAID.', 200);
            }else {
                return $this->error('Ops. something went wrong. Please try again.', 500);
            }  
        } else {            
           return $valid;
        }
    }

    /**
     * @OA\Post(
     *     path="/api/loans/{loan}/pay",
     *     tags={"Loan"},
     *     summary="pays the loan base on terms",
     *     description="Pay the loan base on terms (assumed that it is a weekly repayment)",
     *     operationId="repayment",   
     *      @OA\Parameter(
     *          name="loan",
     *          description="Loan Id",
     *          required = true,
     *          in="path",
     *          @OA\Schema(
     *              type="number"
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="Paid.",
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Code Error",
     *      ),
     *      security={
     *         {"passport": {}}
     *      }   
     * )
     */
    public function repayment(Loan $loan)
    {
        $valid = $this->validateLoan($loan);
        if($valid === true) {
            $balance = $loan->running_balance > 0 ? $loan->running_balance: $loan->amount;
            $principal = $loan->amount / $loan->terms;

            $interest = $balance * ($loan->interest_rate / 100);
            $payment = $principal + $interest;
            $runningBalance = $balance - $principal;

            $attributes['term'] = $loan->transactions()->count() + 1;
            $attributes['principal'] = $principal;
            $attributes['interest'] = $interest;
            $attributes['payment'] = $payment;
            $attributes['balance'] =  $runningBalance;
            
            $transaction = $loan->addTransaction($attributes);

            $result = $loan->update(['running_balance'=> $runningBalance,'total_interest'=>($loan->total_interest + $interest)]);
            
            if($result){
                return $this->success($result,'Record successfully PAID.', 200);
            }else {
                return $this->error('Ops. something went wrong. Please try again.', 500);
            }  
        } else {            
            return $valid;
        }
    }

    /**
     * validates loan record
     * @param  \App\Loan  $loan 
     * @return Mixed
     */
    private function validateLoan($loan)
    {
        if(empty($loan->approved_at)) {
            return $this->error('Loan is not yet approved.',400);
        } else if($loan->total_interest > 0 && $loan->running_balance == 0) {
            return $this->error('Loan is already paid.',400);
        } else {
            return true;
        }
    }

    /**
     * validates incoming request data
     * @param  \Illuminate\Http\Request  $request 
     * @return Array
     */
    private function validateIncomingData($request)
    {
       return Validator::make(
            [
                'amount'=> $request->amount,
                'terms'=> $request->terms,
            ],
            [
                'amount' => "required|regex:/^\d{1,13}(\.\d{1,4})?$/",
                'terms' => 'required|numeric|min:1',
            ]
        );
    }
}
