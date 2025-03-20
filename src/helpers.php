<?php

if(!function_exists("helper")){

	function helper(?string $pkg_name){		

		$mkfn = Strukt\MakeFunc::singleton();

		if(!is_null($mkfn)){

			$ls = null;
			if(!is_null($pkg_name)){

				$ls = $mkfn->get($pkg_name);
				if(!is_null($ls))
					return $ls;
			}

			if(is_null($ls))
				if(in_array($pkg_name, ["packages", "pkg", "pkgs"]) || is_null($pkg_name))
					return $mkfn->listPackages();
		}

		if(is_null($mkfn) && is_null($pkg_name))
			$pkg_name = "base";

		if(!in_array($pkg_name, ["packages", "pkg", "pkgs"]))
			return Strukt\MakeFunc::create($pkg_name);

		return null;
	}
}

if(!function_exists("helper_add")){

	function helper_add(string $fn_name){

		return Strukt\MakeFunc::singleton()->register($fn_name);
	}
}