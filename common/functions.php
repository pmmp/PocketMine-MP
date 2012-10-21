<?php

/*

           -
         /   \
      /         \
   /    POCKET     \
/    MINECRAFT PHP    \
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