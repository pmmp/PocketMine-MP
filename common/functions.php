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


function arg($name, $default){
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

    preg_match_all('/ (--\w+ (?:[= ] [^-]+ [^\s-] )? ) | (-\w+) | (\w+) /x', $args, $match );
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
	//global $path;
	if(!defined("DEBUG") or DEBUG >= $level){
		$message .= $EOL === true ? PHP_EOL:"";
		$message = date("H:i:s"). " ". $message;
		if($log === true and (!defined("LOG") or LOG === true)){
			logg($message, "console", false, $level);
		}	
		echo $message;
	}
}

function error_handler($errno, $errstr, $errfile, $errline){
	console("[ERROR] A level ".$errno." error happened: \"$errstr\" in \"$errfile\" at line $errline", true, true, 0);
}

function logg($message, $name, $EOL = true, $level = 2, $close = false){
	global $fpointers;
	if((!defined("DEBUG") or DEBUG >= $level) and (!defined("LOG") or LOG === true)){
		$message .= $EOL === true ? PHP_EOL:"";
		if(!isset($fpointers)){
			$fpointers = array();
		}
		if(!isset($fpointers[$name])){
			$fpointers[$name] = fopen(FILE_PATH."/".$name.".log", "ab");
		}
		fwrite($fpointers[$name], $message);
		if($close === true){
			fclose($fpointers[$name]);
			unset($fpointers[$name]);
		}
	}
}