<?php
	use MatthiasMullie\Minify;
        define('DIRECTORY', __DIR__);
	require 'vendor/autoload.php';
	require 'php/config.php';

	$css = new Minify\CSS();
	$css->add( CSS . 'bootstrap/bootstrap.min.css' );
	$css->add( CSS . 'pr0verter.css' );
	$css->minify( CSS . 'main.min.css' );

	$js = new Minify\JS();
	$js->add( JS . 'jquery.min.js' );
	$js->add( JS . 'upload_form.js' );
	$js->add( JS . 'jquery.form.min.js' );
	$js->add( JS . 'bootstrap/bootstrap.min.js' );
	$js->minify( JS . 'main.min.js' );

//Flight::sessionStart();

	Flight::route( '/', function() {
		DB::init( DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE );
		if( Flight::is_user_allowed() ) {
			Flight::view()
			      ->assign( 'title', TITLE );
			Flight::view()
			      ->assign( 'base_url', BASE_URL );
			Flight::view()
			      ->display( 'html_header.tpl' );
			Flight::view()
			      ->assign( 'base_url', BASE_URL );
			Flight::view()
			      ->display( 'html_upload_form.tpl' );
			Flight::view()
			      ->display( 'html_footer.tpl' );
		} else {
			Flight::redirect( '/nope' );
		}
	} );

	Flight::route( '/error', function() {
		Flight::view()
		      ->assign( 'title', TITLE );
		Flight::view()
		      ->assign( 'base_url', BASE_URL );
		Flight::view()
		      ->display( 'html_header.tpl' );
		Flight::view()
		      ->assign( 'base_url', BASE_URL );
		Flight::view()
		      ->display( 'html_error.tpl' );
		Flight::view()
		      ->display( 'html_footer.tpl' );
	} );

	Flight::route( '/duration', function() {
		$file_name = Flight::request()->data->file_name;
		$duration  = Flight::request()->data->duration;

		if( $file_name !== NULL && $duration !== NULL ) {
			if( file_exists( DOWNLOAD_PATH . $file_name . '.log' ) ) {
				$file = file_get_contents( DOWNLOAD_PATH . $file_name . '.log' );
				if( $file ) {
					if( strpos( $file, 'muxing overhead' ) !== FALSE ) {
						echo 100;
					} else {
                                                if(strpos($file, 'Conversion failed') !== FALSE){
                                                    echo 420;
                                                    return;
                                                }
						preg_match_all( '/time=(.*?) bitrate/', $file, $last_convert_time );
						$last_convert_time = array_pop( $last_convert_time );
						if( is_array( $last_convert_time ) ) {
							$last_convert_time = array_pop( $last_convert_time );
							$time_array        = array_reverse( explode( ':', $last_convert_time ) );
							$convert_time      = (float)$time_array[ 0 ];
							if( ! empty( $time_array[ 1 ] ) ) {
								$convert_time += ( (int)$time_array[ 1 ] ) * 60;
							}
							if( ! empty( $time_array[ 2 ] ) ) {
								$convert_time += ( (int)$time_array[ 2 ] ) * 60 * 60;
							}
							echo round( ( $convert_time / $duration ) * 100 );
						} else {
							echo 0;
						}
					}
				} else {
					echo 'error';
				}
			} else {
				echo 'error';
			}
		} else {
			echo 'error';
		}
	} );

	Flight::route( '/status', function() {
		$random_name = Flight::request()->data->random_name;
		$format      = Flight::request()->data->format;
		$duration    = Flight::request()->data->duration;
		if( $random_name !== NULL && $format !== NULL && $duration !== NULL ) {
			while( TRUE ) {
				if( file_exists( DOWNLOAD_PATH . $random_name . '.log' ) ) {
					break;
				}
				sleep( 1 );
			}
			Flight::view()
			      ->assign( 'title', TITLE );
			Flight::view()
			      ->assign( 'base_url', BASE_URL );
			Flight::view()
			      ->display( 'html_header.tpl' );
			Flight::view()
			      ->assign( 'duration', $duration );
			Flight::view()
			      ->assign( 'file_name', $random_name );
			Flight::view()
			      ->assign( 'base_url', BASE_URL );
			Flight::view()
			      ->display( 'html_status.tpl' );
			Flight::view()
			      ->display( 'html_footer.tpl' );
		} else {
			Flight::redirect( '/' );
		}
	} );

	Flight::route( '/show/@file', function( $file ) {
		if( file_exists( DOWNLOAD_PATH . $file . '.mp4' ) ) {
			Flight::view()
			      ->assign( 'title', TITLE );
			Flight::view()
			      ->assign( 'base_url', BASE_URL );
			Flight::view()
			      ->display( 'html_header.tpl' );
			Flight::view()
			      ->assign( 'file', $file );
			Flight::view()
			      ->assign( 'base_url', BASE_URL );
			Flight::view()
			      ->display( 'html_show.tpl' );
			Flight::view()
			      ->display( 'html_footer.tpl' );
		} else {
			Flight::redirect( '/' );
		}
	} );

	Flight::route( '/download/@file', function( $file ) {
		if( file_exists( DOWNLOAD_PATH . $file . '.mp4' ) ) {
			header( 'Content-type: video' );
			header( 'Content-Disposition: attachment; filename="' . basename( $file . '.mp4' ) . '"' );
			header( 'Content-Transfer-Encoding: binary' );
			readfile( DOWNLOAD_PATH . $file . '.mp4' );
		} else {
			Flight::redirect( '/' );
		}
	} );

	Flight::route( '/upload', function() {
		
		DB::init( DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE );
		if( Flight::is_user_allowed() ) {
			$file  = Flight::request()->data->file;
			$url   = Flight::request()->data->url;
			$limit = Flight::request()->data->limit;
			$sound = Flight::request()->data->sound;
                        $autoResolution = Flight::request()->data->autoResolution;

			if( $limit > 0 && $limit <= 30 ) {
				$random_name = Flight::random_string();
				$max_size    = $limit * 8192;
				if( $url !== '' ) {
					$format = substr( $url, strrpos( $url, '.' ) + 1 );
					if( Flight::get_url_file_size( $url ) < 104857600 ) {
						if( Flight::is_supported( $format ) ) {
							Flight::download( $url, DOWNLOAD_PATH . $random_name . '.' . $format );
							Flight::convert( $random_name, $format, $max_size, $limit, $sound, $autoResolution );
						} else {
							Flight::redirect( '/error' );
						}
					} else {
						Flight::redirect( '/error' );
					}
				} elseif( $file !== '' ) {
					$format = pathinfo( Flight::request()->files->file[ 'name' ], PATHINFO_EXTENSION );
					if( Flight::is_supported( $format ) ) {
						if( Flight::request()->files->file[ 'size' ] < 104857600 ) {
							
							if( move_uploaded_file( Flight::request()->files->file[ 'tmp_name' ], DOWNLOAD_PATH . $random_name . '.' . $format ) ) {
								
								Flight::convert( $random_name, $format, $max_size, $limit, $sound, $autoResolution );
								
							} else {
								Flight::redirect( '/error' );
							}
						} else {
							Flight::redirect( '/error' );
						}
					} else {
						Flight::redirect( '/error' );
					}
				} else {
					Flight::redirect( '/' );
				}
			}
		} else {
			Flight::redirect( '/nope' );
		}
	} );
        
        Flight::route( '/help', function(){
            Flight::view()
			      ->assign( 'title', TITLE );
			Flight::view()
			      ->assign( 'base_url', BASE_URL );
			Flight::view()
			      ->display( 'html_header.tpl' );
			Flight::view()
			      ->assign( 'base_url', BASE_URL );
			Flight::view()
			      ->display( 'html_help.tpl' );
			Flight::view()
			      ->display( 'html_footer.tpl' );
        });
        
        Flight::route( '/contact', function(){
            Flight::view()
			      ->assign( 'title', TITLE );
			Flight::view()
			      ->assign( 'base_url', BASE_URL );
			Flight::view()
			      ->display( 'html_header.tpl' );
			Flight::view()
			      ->assign( 'base_url', BASE_URL );
			Flight::view()
			      ->display( 'html_contact.tpl' );
			Flight::view()
			      ->display( 'html_footer.tpl' );
        });
        
        Flight::route( '/support', function(){
            Flight::view()
			      ->assign( 'title', TITLE );
			Flight::view()
			      ->assign( 'base_url', BASE_URL );
			Flight::view()
			      ->display( 'html_header.tpl' );
			Flight::view()
			      ->assign( 'base_url', BASE_URL );
			Flight::view()
			      ->display( 'html_support.tpl' );
			Flight::view()
			      ->display( 'html_footer.tpl' );
        });

	Flight::route( '/nope', function() {
		DB::init( DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE );
		if( Flight::is_user_allowed() ) {
			Flight::redirect( '/' );
		} else {
			$user_ip   = md5( Flight::request()->ip );
			$user_data = Flight::select( 'pr0verter', '*', 'tstamp = ' . "'" . $user_ip . "'", 1 );
			$wait_time = TIME_TO_WAIT - ( time() - $user_data[ 0 ][ 'datetime' ] );
			Flight::view()
			      ->assign( 'title', TITLE );
			Flight::view()
			      ->assign( 'base_url', BASE_URL );
			Flight::view()
			      ->display( 'html_header.tpl' );
			Flight::view()
			      ->assign( 'wait_time', $wait_time );
			Flight::view()
			      ->assign( 'base_url', BASE_URL );
			Flight::view()
			      ->display( 'html_countdown.tpl' );
			Flight::view()
			      ->display( 'html_footer.tpl' );
		}
	} );

	Flight::route( '/*', function() {
		Flight::redirect( '/' );
	} );

	Flight::start();
