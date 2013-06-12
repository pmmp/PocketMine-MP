<?php

/*

           -
         /   \
      /         \
   /   PocketMine  \
/          MP         \
|\     @shoghicp     /|
|.   \           /   .|
| ..     \   /     .. |
|    ..    |    ..    |
|       .. | ..       |
\          |          /
   \       |       /
      \    |    /
         \ | /

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.


*/

if(!function_exists("cli_set_process_title")){
	function cli_set_process_title($title){
		if(ENABLE_ANSI === true){
			echo "\x1b]0;".$title."\x07";
			return true;
		}else{
			return false;
		}
	}
}

function dummy(){
	
}

function safe_var_dump($var, $cnt = 0){
	switch(true){
		case is_array($var):
			echo str_repeat("  ", $cnt)."array(".count($var).") {".PHP_EOL;
			foreach($var as $key => $value){
				echo str_repeat("  ", $cnt + 1)."[".(is_integer($key) ? $key:'"'.$key.'"')."]=>".PHP_EOL;
				safe_var_dump($value, $cnt + 1);
			}
			echo str_repeat("  ", $cnt)."}".PHP_EOL;
			break;
		case is_integer($var):
			echo str_repeat("  ", $cnt)."int(".$var.")".PHP_EOL;
			break;
		case is_float($var):
			echo str_repeat("  ", $cnt)."float(".$var.")".PHP_EOL;
			break;
		case is_bool($var):
			echo str_repeat("  ", $cnt)."bool(".($var === true ? "true":"false").")".PHP_EOL;
			break;
		case is_string($var):
			echo str_repeat("  ", $cnt)."string(".strlen($var).") \"$var\"".PHP_EOL;
			break;
		case is_resource($var):
			echo str_repeat("  ", $cnt)."resource() of type (".get_resource_type($var).")".PHP_EOL;
			break;
		case is_object($var):
			echo str_repeat("  ", $cnt)."object(".get_class($var).")".PHP_EOL;
			break;
		case is_null($var):
			echo str_repeat("  ", $cnt)."NULL".PHP_EOL;
			break;
	}
}

function kill($pid){
	switch(Utils::getOS()){
		case "win":
			exec("taskkill.exe /F /PID ".((int) $pid)." > NUL");
			break;
		case "mac":
		case "linux":
		default:
			exec("kill -9 ".((int) $pid)." > /dev/null 2>&1");
	}
}


function require_all($path, &$count = 0){
	$dir = dir($path."/");
	$dirs = array();
	while(false !== ($file = $dir->read())){
		if($file !== "." and $file !== ".."){
			if(!is_dir($path.$file) and strtolower(substr($file, -3)) === "php"){
				require_once($path.$file);
				++$count;
			}elseif(is_dir($path.$file)){
				$dirs[] = $path.$file."/";
			}
		}
	}
	foreach($dirs as $dir){
		require_all($dir, $count);
	}

}

function hard_unset(&$var){
	if(is_object($var)){
		$unset = new ReflectionClass($var);
		foreach($unset->getProperties() as $prop){
			$prop->setAccessible(true);
			@hard_unset($prop->getValue($var));
			$prop->setValue($var, null);
		}
		$var = null;
		unset($var);
	}elseif(is_array($var)){
		foreach($var as $i => $v){
			hard_unset($var[$i]);
		}
		$var = null;
		unset($var);
	}else{
		$var = null;
		unset($var);
	}
}


function parseNBTData($data){
	$x = array();
	if(isset($data["value"])){
		return parseNBTData($data["value"]);
	}
	foreach($data as $d){
		if(!isset($d["value"]) and is_array($d) and count($d) == 1){
			return parseNBTData(array_pop($d));
		}elseif(!isset($d["value"]) and is_array($d)){
			$x[] = parseNBTData($d);
		}elseif(is_array($d["value"]) and isset($d["name"])){
			$x[$d["name"]] = parseNBTData($d["value"]);
		}elseif(is_array($d["value"]) and $d["type"] == 10){
			return parseNBTData($d["value"]);
		}elseif($d["name"] != ""){
			$x[$d["name"]] = $d["value"];
		}
	}
	if(count($x) == 0){
		$x = $data;
	}
	return $x;
}


function arg($name, $default = false){
	global $arguments, $argv;
	if(!isset($arguments)){
		$arguments = arguments($argv);
	}

	if(isset($arguments["commands"][$name])){
		return $arguments["commands"][$name];
	}else{
		return $default;
	}
}

function arguments ( $args ){
	if(!is_array($args)){
		$args = array();
	}
    array_shift( $args );
    $args = join( $args, ' ' );

    preg_match_all('/ (--[\w\-]+ (?:[= ] [^-]+ [^\s-] )? ) | (-\w+) | (\w+) /x', $args, $match );
    $args = array_shift( $match );

    $ret = array(
        'input'    => array(),
        'commands' => array(),
        'flags'    => array()
    );

    foreach ( $args as $arg ) {

        // Is it a command? (prefixed with --)
        if ( substr( $arg, 0, 2 ) === '--' ) {

            $value = preg_split( '/[= ]/', $arg, 2 );
            $com   = substr( array_shift($value), 2 );
            $value = join($value);

            $ret['commands'][$com] = !empty($value) ? $value : true;
            continue;

        }

        // Is it a flag? (prefixed with -)
        if ( substr( $arg, 0, 1 ) === '-' ) {
            $ret['flags'][] = substr( $arg, 1 );
            continue;
        }

        $ret['input'][] = $arg;
        continue;

    }

    return $ret;
}

function console($message, $EOL = true, $log = true, $level = 1){
	if(!defined("DEBUG") or DEBUG >= $level){
		$message .= $EOL === true ? PHP_EOL:"";
		$time = (ENABLE_ANSI === true ? "\x1b[36m".date("H:i:s")."\x1b[0m":date("H:i:s")) . " ";
		$replaced = preg_replace('/\x1b\[[0-9;]*m/', "", $time . $message);
		if($log === true and (!defined("LOG") or LOG === true)){
			logg($replaced, "console", false, $level);
		}
		if(ENABLE_ANSI === true){
			$add = "";
			if(preg_match("/\[([a-zA-Z0-9]*)\]/", $message, $matches) > 0){
				$add .= "\x1b";
				switch($matches[1]){
					case "ERROR":
						$add .= "[31;1m";
						break;
					case "INTERNAL":
					case "DEBUG":
						$add .= "[30;1m";
						break;
					case "WARNING":
						$add .= "[33;1m";
						break;
					case "NOTICE":
						$add .= "[37;1m";
						break;
					default:
						$add = "";
						break;
				}
			}
			$message = $time . $add . $message . "\x1b[0m";
		}else{
			$message = $replaced;
		}
		echo $message;
	}
}

function error_handler($errno, $errstr, $errfile, $errline){
	if(error_reporting() === 0){ //@ error-control
		return false;
	}
	console("[ERROR] A level ".$errno." error happened: \"$errstr\" in \"$errfile\" at line $errline", true, true, 0);
	return true;
}

function logg($message, $name, $EOL = true, $level = 2, $close = false){
	global $fpointers;
	if((!defined("DEBUG") or DEBUG >= $level) and (!defined("LOG") or LOG === true)){
		$message .= $EOL === true ? PHP_EOL:"";
		if(!isset($fpointers)){
			$fpointers = array();
		}
		if(!isset($fpointers[$name]) or $fpointers[$name] === false){
			$fpointers[$name] = @fopen(DATA_PATH."/".$name.".log", "ab");
		}
		@fwrite($fpointers[$name], $message);
		if($close === true){
			fclose($fpointers[$name]);
			unset($fpointers[$name]);
		}
	}
}