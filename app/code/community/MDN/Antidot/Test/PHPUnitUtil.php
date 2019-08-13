<?php
class MDN_Antidot_Test_PHPUnitUtil
{
	public static function callPrivateMethod($obj, $name, array $args) {
		$class = new \ReflectionClass($obj);
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method->invokeArgs($obj, $args);
	}
}