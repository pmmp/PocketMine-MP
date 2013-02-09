<?php

require_once(dirname(__FILE__)."/../src/common/dependencies.php");

class BinaryRWTest extends PHPUnit_Framework_TestCase{
	public function testRead(){
		$this->assertTrue(Utils::readTriad("\x02\x01\x03") === 131331, "Utils::readTriad");
		$this->assertTrue(Utils::readInt("\xff\x02\x01\x03") === -16645885, "Utils::readInt");
		$this->assertTrue(abs(Utils::readFloat("\x49\x02\x01\x03") - 532496.1875) < 0.0001, "Utils::readFloat");
		$this->assertTrue(abs(Utils::readDouble("\x41\x02\x03\x04\x05\x06\x07\x08") - 147552.5024529) < 0.0001, "Utils::readDouble");
		$this->assertTrue(Utils::readLong("\x41\x02\x03\x04\x05\x06\x07\x08") === "4684309878217770760", "Utils::readLong");
		$item = Utils::readSlot("\x00\x09\x08\x00\x00");
		$this->assertTrue(($item instanceof Item) and $item->getID() === STILL_WATER and $item->count === 8 and $item->getMetadata() === 0, "Utils::readSlot");
	}
}