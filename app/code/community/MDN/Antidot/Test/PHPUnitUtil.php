<?php
class MDN_Antidot_Test_PHPUnitUtil
{
	public static function callPrivateMethod($obj, $name, array $args) {
		$class = new \ReflectionClass($obj);
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method->invokeArgs($obj, $args);
	}

    public static function getPrivateProperty($obj, $name) {
        $class = new \ReflectionClass($obj);
        $property = $class->getProperty($name);
		$property->setAccessible(true);
		return $property->getValue($obj);
	}

}