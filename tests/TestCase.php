<?php

namespace Tests;


use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use App\User;
use App\Loan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $user;
    protected $loan;

    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('passport:install');

        $this->user = factory(User::class)->create();
        
        $this->actingAs($this->user,'api');

        $this->loan = factory(Loan::class)->create();
    }
}
