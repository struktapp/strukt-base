<?php

use Strukt\Env;

Env::withFile("fixture/.env");

test("env[from_file]", function(){

	expect(env("allow_admin"))->toBeEmpty();
	expect("p@55w0rd"  == env("password"))->toBeTrue();
});

test("env[for_comment]", function(){

	expect(Env::has("allow_ssl"))->toBeFalse();
});

test("env[get_set]", function(){

	$framework = "Strukt";

	env("framework", $framework);

	expect($framework == env("framework"))->toBeTrue();
});
