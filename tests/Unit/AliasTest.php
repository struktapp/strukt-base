<?php

use Strukt\Alias;

test('alias', function (){

    Alias::set("au.ctr.User", Payroll\AuthModule\Controller\User::class);
    expect(Alias::get("au.ctr.User"))->toBe(Payroll\AuthModule\Controller\User::class);
});
