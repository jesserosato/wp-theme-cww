<?php

class JmrDebugger
{
	protected $file;
	protected $path;
	protected $msgs;
	
	function __construct($filename)
	{
		if ( !preg_match( '/\.txt$/', $filename ) )
			throw new Exception("JmrDebugger requires a .txt filename.");
		
		$this->path = defined( ABSPATH ) ? ABSPATH : $_SERVER['DOCUMENT_ROOT'];
		$this->path .= '/wp-content/' . $filename;
		
		// Check that file was opened
		if ( !( $this->file = fopen( $this->path, "a" ) ) )
			throw new Exception("Error opening file at " . $this->path . ".");
	}
	
	function __destruct()
	{
		foreach( $this->msgs as $key => $msg ) {
			$msg = "[" . $key . "] " . $msg . "\n";
			if ( !fwrite( $this->file, $msg ) )
				throw new Exception("Error writing to file (" . $this->path . ").");
		}
		fclose(file);
	}
	
	public function debug_log( $string )
	{
		if ( is_array( $string ) )
			$string = print_r($string, true);
		
		if ( !is_string( $string ) )
			throw new Exception("Function JmrDebugger::debug_log() requires string or array parameter.");
		 
		$this->msgs[date('Y-m-d H:i')] = $string;
	}
}