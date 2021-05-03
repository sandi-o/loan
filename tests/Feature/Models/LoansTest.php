<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoansTest extends TestCase
{
    use WithFaker,RefreshDatabase;

    public function test_user_has_loans()
    {               
        $this->getJson("api/loans")->assertStatus(200)->assertJsonStructure(['status','message','data']);
    }

    public function test_user_applies_for_a_loan()
    {
        $loan = ['description' => 'Whatever purpose','amount' => 10000,'terms'=>4];

        $this->postJson("api/loans",$loan)->assertStatus(201)->assertJsonStructure(['status','message','data']);
    }

    public function test_user_updates_the_pending_loan()
    {
        $patchData = ['description' => 'Whatever purpose 2','amount' => 7500,'terms'=>3];

        $this->patchJson("api/loans/{$this->loan->id}",$patchData)->assertStatus(200)->assertJsonStructure(['status','message','data']);
    }

    public function test_approve_loan_of_user()
    {
        $this->patchJson("api/loans/{$this->loan->id}/approve")->assertStatus(200)->assertJsonStructure(['status','message','data']);
    }

    public function test_user_fully_paid_for_the_loan()
    {        
        $this->test_approve_loan_of_user();
        $this->postJson("api/loans/{$this->loan->id}/full/{$this->loan->terms}")->assertStatus(200)->assertJsonStructure(['status','message','data']);
    }

    public function test_user_paid_for_the_loan()
    {        
        $this->test_approve_loan_of_user();
        $this->postJson("api/loans/{$this->loan->id}/pay")->assertStatus(200)->assertJsonStructure(['status','message','data']);
    }
}
