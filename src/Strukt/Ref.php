<?php

namespace Strukt;

class Ref{

	private $class;
	private $instance;

	/**
	 * @param \Reflector $class
	 */
	public function __construct(\Reflector $class){

		$this->class = $class;
	}

	/**
	 * @param string|callable $name_or_func
	 * 
	 * @return mixed
	 */
	public static function func(string|callable $name_or_func):mixed{

		$rFunc = new \ReflectionFunction($name_or_func);

		return new class($rFunc){

			private $oFunc;

			/**
			 * @param ReflectionFunction $func
			 */
			public function __construct(\ReflectionFunction $func){

				$this->oFunc = $func;
			}

			/**
			 * @return ReflectionFunction
			 */
			public function getRef():\ReflectionFunction{

				return $this->oFunc;
			}

			/**
			 * @param mixed ...
			 * 
			 * @return mixed
			 */
			public function invoke(...$args):mixed{

				if(is_null($args))
					$result = $this->oFunc->invoke();
				else
					$result = $this->oFunc->invokeArgs($args);

				return $result;
			}
		};
	}

	/**
	* Create reflection from object instance
	* 
	* @param object $instance
	* 
	* @return static
	*/
	public static function createFrom(object $instance):static{

		$class = new \ReflectionObject($instance);

		$ref = new self($class);

		$ref->readyMade($instance);

		return $ref;
	}

	/**
	 * @param string $classname
	 * 
	 * @return static
	 */
	public static function create(string $classname):static{		

		$class = new \ReflectionClass($classname);

		return new self($class);
	}

	/**
	 * @param string $name
	 * 
	 * @return mixed
	 */
	public function method(string $name):mixed{

		$rMethod = $this->class->getMethod($name);

		return new class($rMethod, $this->instance){

			private $oMethod;
			private $oInstance;

			public function __construct(\ReflectionMethod $method, object $instance){

				$this->oMethod = $method;
				$this->oInstance = $instance;
			}

			/**
			 * @return \ReflectionMethod
			 */
			public function getRef():\ReflectionMethod{

				return $this->oMethod;
			}

			/**
			 * @return \Closure
			 */
			public function getClosure():\Closure{

				return $this->oMethod->getClosure($this->oInstance);
			}

			/**
			 * @param mixed ...
			 * 
			 * @return mixed
			 */
			public function invoke(...$args):mixed{

				if(is_null($args))
					$result = $this->oMethod->invoke($this->oInstance);
				else
					$result = $this->oMethod->invokeArgs($this->oInstance, $args);

				return $result;
			}
		};
	}

	/**
	 * @param string $name
	 * 
	 * @return mixed
	 */
	public function prop(string $name):mixed{

		$rProp = $this->class->getProperty($name);

		return new class($rProp, $this->instance){

			private $rProp;
			private $oInstance;

			public function __construct(\ReflectionProperty $prop, object $instance){

				$this->rProp = $prop;
				$this->oInstance = $instance;
			}

			/**
			 * @return \ReflectionProperty
			 */
			public function getRef():\ReflectionProperty{

				return $this->rProp;
			}

			/**
			 * @param mixed $value
			 * 
			 * @return void
			 */
			public function set(mixed $value):void{

				$this->rProp->setValue($this->oInstance, $value);
			}

			/**
			 * @return mixed
			 */
			public function get():mixed{

				return $this->rProp->getValue($this->oInstance);
			}
		};
	}

	/**
	* @return \ReflectionClass
	*/
	public function getRef():\ReflectionClass{

		return $this->class;
	}

	/**
	 * @return object
	 */
	public function getInstance():object{

		return $this->instance;
	}

	/**
	 * @param object $instance
	 * 
	 * @return object
	 */
	public function readyMade(object $instance):object{

		return $this->instance = $instance;
	}

	/**
	* newInstance
	* 
	* @param mixed ...
	* 
	* @return static
	*/
	public function make(...$args):static{

		$this->instance = $this->class->newInstance($args);

		return $this;
	}

	/**
	* newInstanceArgs
	* 
	* @param array $args
	* 
	* @return static
	*/
	public function makeArgs(array $args):static{

		$this->instance = $this->class->newInstanceArgs($args);

		return $this;
	}

	/**
	* newInstanceWithoutConstructor
	* 
	* @return static
	*/
	public function noMake():static{

		$this->instance = $this->class->newInstanceWithoutConstructor();

		return $this;
	}
}