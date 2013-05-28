<?php
namespace Zh\Config;
use Zend\Config as ZendConfig;

/**
 *
 *
 * @author Abel
 * @version 0.0.1
 * 2013-4-16
 */
class Factory extends ZendConfig\Factory{

	private static $fac =null;

	public static function getServerArray($realConfigFile,$section){
		$serverArray = ZendConfig\Factory::fromFile($realConfigFile);
		if(!isset($serverArray[$section])){
			throw new ConfigException('Can not found the ('.$section.') in ('.$realConfigFile.')!');
		}
		return $serverArray[$section];
	}
}