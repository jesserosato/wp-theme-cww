<?php
// Require files within a given directory
function require_files( $path, $reg_exp, $recursive = true )
{
	try {
		$dir = new DirectoryIterator($path);
		
		foreach ( $dir as $file ) {
			if ( $file->isDot() || preg_match('/.DS_Store/', $file->getFilename()))
				continue;
		    if ( $file->isDir() && $recursive )
			   require_files($file->getPathname(), $reg_exp, $recursive);
		    else if ( preg_match( $reg_exp, $file->getFilename() ) )
			    require_once($file->getPathname());
		}
	} catch( Exception $e ) {
		return false;
	}
}