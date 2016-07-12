<?php
	spl_autoload_register( NULL, FALSE );
	spl_autoload_extensions( '.php, .class.php' );

	function classLoader( $class ) {
		$filename = strtolower( $class ) . '.class.php';
		$file     = CLASSES . $filename;
		if( ! file_exists( $file ) ) {
			return FALSE;
		}
		include $file;

		return TRUE;
	}

	spl_autoload_register( 'classLoader' );
