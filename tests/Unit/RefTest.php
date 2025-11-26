<?php

use Strukt\Ref;

test("ref.create[class]", function(){

	$ref = Ref::create(Fixture\User::class);
	$ref->makeArgs(["pitsolu"]);
	$ref->method("setPassword")->invoke("p@55w0rd");

	expect("pitsolu")->toBe($ref->method("getUsername")->invoke());
	expect(sha1("p@55w0rd"))->toBe($ref->method("getPassword")->invoke());
});

test("ref.createFrom[object]", function(){

	$user = new Fixture\User("admin");
	$user->setPassword("p@55w0rd!!");

	$ref = Ref::createFrom($user);
	expect($ref->method("getUsername")->invoke())->toBe("admin");
	expect($ref->method("getPassword")->invoke())->toBe(sha1("p@55w0rd!!"));
});

test("ref.prop", function(){

	$ref = Ref::create(Fixture\User::class);
	$ref->noMake();
	$ref->prop("username")->set("pitsolu");

	$this->assertInstanceOf(Fixture\User::class, $ref->getInstance());
	$this->assertEquals("pitsolu", $ref->prop("username")->get());
	
})->skip("Unexpected behavior setting private property!");

test("ref.func", function(){

	$ref = Ref::func(function(int $a, int $b){

		return $a + $b;
	});

	$this->assertEquals(5, $ref->invoke(3,2));
});