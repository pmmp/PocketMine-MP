<?php
/***REM_START***/
		$testErrors = 0;
		function testCase($name, $output, $expected){
			global $testErrors;
			if($output === $expected){
				console("[TEST] $name: ".FORMAT_GREEN."Ok.");
			}else{
				console("[TEST] $name: ".FORMAT_RED."Error.");
				console("Expected ".print_r($expected, true).", got ".print_r($output, true));
				++$testErrors;
			}
		}
		
		if(!class_exists("PocketMinecraftServer", false)){
			define("NO_THREADS", true);
			require_once(dirname(__FILE__)."/../dependencies.php");
			require_once(FILE_PATH."/src/functions.php");
			require_once(FILE_PATH."/src/dependencies.php");
			console(FORMAT_GREEN . "[TEST] Starting tests");
			testCase("dummy", dummy(), null);
			$t = new ServerSuiteTest;
			echo PHP_EOL;
			if($testErrors === 0){
				console(FORMAT_GREEN . "[TEST] No errors. Test complete.");
				exit(0);
			}else{
				console(FORMAT_RED . "[TEST] Errors found.");
				exit(1);
			}
		}

		class ServerSuiteTest {
			public function __construct(){			
				//binary things
				testCase("Utils::readTriad", Utils::readTriad("\x02\x01\x03"), 131331);
				testCase("Utils::readInt", Utils::readInt("\xff\x02\x01\x03"), -16645885);
				testCase("Utils::readFloat", abs(Utils::readFloat("\x49\x02\x01\x03") - 532496.1875) < 0.0001, true);
				testCase("Utils::readDouble", abs(Utils::readDouble("\x41\x02\x03\x04\x05\x06\x07\x08") - 147552.5024529) < 0.0001, true);
				testCase("Utils::readTriad", Utils::readLong("\x41\x02\x03\x04\x05\x06\x07\x08"), "4684309878217770760");
				
				//PocketMine-MP server startup
				global $server;
				$server = new ServerAPI();
				$server->load();
				testCase("event attached", is_integer($server->event("server.start", array($this, "hook"))), true);
				$server->init();
			}
			
			public function hook(){
				testCase("event fired", true, true);
				$server = ServerAPI::request();
				testCase("defaultgamemode", $server->getGamemode(), "survival");
				
				
				//Everything done!
				$server->close();
			}
		}
/***REM_END***/