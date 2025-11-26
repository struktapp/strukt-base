<?php
namespace Fixture;

class User{

	public $id;
	private $username;
	private $password;

	public function __construct(string $username){

		$this->username = $username;
	}

	public function getUsername(){
		
		return $this->username;
	}

	public function getPassword(){
		
		return $this->password;
	}

	public function setPassword($password){
		
		$this->password = sha1($password);
	}
}