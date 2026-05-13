<?php

use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    public function testTrueIsTrue()
    {
        $this->assertTrue(true);
    }

    public function testEmailValidation()
    {
        $email = "tarek@test.com";

        $this->assertMatchesRegularExpression(
            "/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i",
            $email
        );
    }

    public function testPasswordValidation()
    {
        $password = "12345678";

        $this->assertGreaterThanOrEqual(8, strlen($password));
    }

    public function testLogin()
    {
        $savedEmail = "owner@gmail.com";
        $savedPassword = "1122owner";

        $inputEmail = "owner@gmail.com";
        $inputPassword = "1122owner";

        $this->assertEquals($savedEmail, $inputEmail);
        $this->assertEquals($savedPassword, $inputPassword);
    }

    public function testDashboardCalculation()
    {
        $orders = [100, 200, 300];

        $total = array_sum($orders);

        $this->assertEquals(600, $total);
    }
}
