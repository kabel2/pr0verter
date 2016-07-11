<?php

	/*
	  Copyright (c) 2009, Robert Hafner
	  All rights reserved.

	  Redistribution and use in source and binary forms, with or without modification, are permitted provided that the
	  following conditions are met:

	 * Redistributions of source code must retain the above copyright notice, this list of conditions and the following
	  disclaimer.
	 * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the
	  following disclaimer in the documentation and/or other materials provided with the distribution.
	 * Neither the name of the <ORGANIZATION> nor the names of its contributors may be used to endorse or promote
	  products derived from this software without specific prior written permission.

	  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
	  INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
	  DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
	  SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
	  SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
	  WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
	  OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

	 */

	/**
	 * This SessionManager starts starts the php session (regardless of which handler is set) and secures it by locking down
	 * the cookie, restricting the session to a specific host and browser, and regenerating the ID.
	 */
	class Session {

		/**
		 * AOL users may switch IP addresses from one proxy to another.
		 *
		 * @link http://webmaster.info.aol.com/proxyinfo.html
		 * @var array
		 */
		protected static $aolProxies = [ '195.93.', '205.188', '198.81.', '207.200', '202.67.', '64.12.9' ];

		/**
		 * This function starts, validates and secures a session.
		 *
		 * @param string $name   The name of the session.
		 * @param int    $limit  Expiration date of the session cookie, 0 for session only
		 * @param string $path   Used to restrict where the browser sends the cookie
		 * @param string $domain Used to allow subdomains access to the cookie
		 * @param bool   $secure If true the browser only sends the cookie over https
		 */
		public static function sessionStart( $name, $limit = 0, $path = '/', $domain = NULL, $secure = NULL ) {
			// Set the cookie name
			session_name( $name );

			// Set SSL level
			$https = $secure !== NULL ? $secure : array_key_exists( 'HTTPS', $_SERVER );

			// Set session cookie options
			session_set_cookie_params( $limit, $path, $domain, $https, TRUE );
			session_start();

			// Make sure the session hasn't expired, and destroy it if it has
			if( static::validateSession() ) {
				// Check to see if the session is new or a hijacking attempt
				if( ! static::preventHijacking() ) {
					// Reset session data and regenerate id
					$_SESSION                = [ ];
					$_SESSION[ 'IPaddress' ] = array_key_exists( 'HTTP_X_FORWARDED_FOR', $_SERVER ) ? $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] : $_SERVER[ 'REMOTE_ADDR' ];
					$_SESSION[ 'userAgent' ] = $_SERVER[ 'HTTP_USER_AGENT' ];
					if( ! array_key_exists( 'login', $_SESSION ) ) {
						$_SESSION[ 'login' ] = FALSE;
					}
					static::regenerateSession();

					// Give a 5% chance of the session id changing on any request
				} elseif( mt_rand( 1, 100 ) <= 5 ) {
					static::regenerateSession();
				}
			} else {
				$_SESSION = [ ];
				session_destroy();
				session_start();
			}
		}

		/**
		 * This function is used to see if a session has expired or not.
		 *
		 * @return bool
		 */
		static protected function validateSession() {
			if( array_key_exists( 'OBSOLETE', $_SESSION ) && ! array_key_exists( 'EXPIRES', $_SESSION ) ) {
				return FALSE;
			}

			return ! ( array_key_exists( 'EXPIRES', $_SESSION ) && $_SESSION[ 'EXPIRES' ] < time() );
		}

		/**
		 * This function checks to make sure a session exists and is coming from the proper host. On new visits and hacking
		 * attempts this function will return false.
		 *
		 * @return bool
		 */
		static protected function preventHijacking() {
			if( ! array_key_exists( 'IPaddress', $_SESSION ) || ! array_key_exists( 'userAgent', $_SESSION ) ) {
				return FALSE;
			}


			if( $_SESSION[ 'userAgent' ] !== $_SERVER[ 'HTTP_USER_AGENT' ] && ! ( strpos( $_SESSION[ 'userAgent' ], 'Trident' ) !== FALSE && strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'Trident' ) !== FALSE ) ) {
				return FALSE;
			}

			$sessionIpSegment = substr( $_SESSION[ 'IPaddress' ], 0, 7 );

			$remoteIpHeader = array_key_exists( 'HTTP_X_FORWARDED_FOR', $_SERVER ) ? $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] : $_SERVER[ 'REMOTE_ADDR' ];

			$remoteIpSegment = substr( $remoteIpHeader, 0, 7 );

			if( $_SESSION[ 'IPaddress' ] !== $remoteIpHeader && ! ( in_array( $sessionIpSegment, static::$aolProxies, TRUE ) && in_array( $remoteIpSegment, static::$aolProxies, TRUE ) ) ) {
				return FALSE;
			}

			return ! ( $_SESSION[ 'userAgent' ] !== $_SERVER[ 'HTTP_USER_AGENT' ] );
		}

		/**
		 * This function regenerates a new ID and invalidates the old session. This should be called whenever permission
		 * levels for a user change.
		 */
		public static function regenerateSession() {
			// If this session is obsolete it means there already is a new id
			//if(isset($_SESSION['OBSOLETE']) || $_SESSION['OBSOLETE'] == true)
			//	return;
			if( array_key_exists( 'OBSOLETE', $_SESSION ) ) {
				if( $_SESSION[ 'OBSOLETE' ] === TRUE ) {
					return;
				}

				return;
			}

			// Set current session to expire in 10 seconds
			$_SESSION[ 'OBSOLETE' ] = TRUE;
			$_SESSION[ 'EXPIRES' ]  = time() + 10;

			// Create new session without destroying the old one
			session_regenerate_id( FALSE );

			// Grab current session ID and close both sessions to allow other scripts to use them
			$newSession = session_id();
			session_write_close();

			// Set session ID to the new one, and start it back up again
			session_id( $newSession );
			session_start();

			// Now we unset the obsolete and expiration values for the session we want to keep
			unset( $_SESSION[ 'EXPIRES' ], $_SESSION[ 'OBSOLETE' ] );
		}

		public static function sessionStop() {
			session_unset();
			session_destroy();
			$_SESSION = [ ];
		}

	}