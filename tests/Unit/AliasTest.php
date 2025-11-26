<?php

use Strukt\Alias;

test('alias[get_set]', function (){

    Alias::set("au.ctr.User", Payroll\AuthModule\Controller\User::class);
    expect(Alias::get("au.ctr.User"))->toBe(Payroll\AuthModule\Controller\User::class);
});
