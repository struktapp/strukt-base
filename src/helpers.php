<?php

if(!function_exists("helper")){

	function helper(string $pkg_name){		

		$mkfn = Strukt\MakeFunc::singleton();

		if(!is_null($mkfn)){

			$ls = $mkfn->get($pkg_name);
			if(!is_null($ls))
				return $ls;

			if(is_null($ls))
				if(in_array($pkg_name, ["packages", "pkg", "pkgs"]))
					return $mkfn->listPackages();
		}

		return Strukt\MakeFunc::create($pkg_name);
	}
}

if(!function_exists("helper_add")){

	function helper_add(string $fn_name){

		return Strukt\MakeFunc::singleton()->register($fn_name);
	}
}