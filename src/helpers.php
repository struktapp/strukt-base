<?php

use Strukt\Base\Helper as BaseHelper;

if(!function_exists("helper")){

	/**
	 * @param string $pkg_name
	 * 
	 * @return BaseHelper|array|null
	 */
	function helper(?string $pkg_name = null):BaseHelper|array|null{		

		$mkfn = BaseHelper::singleton();

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
			return BaseHelper::create($pkg_name);

		return null;
	}
}

if(!function_exists("helper_add")){

	/**
	 * @param string $fn_name
	 * 
	 * @return boolean
	 */
	function helper_add(string $fn_name):bool{

		return BaseHelper::singleton()->register($fn_name);
	}
}