<?php

	/**
	 * Class DB
	 */
	class DB {
		/**
		 * @var mysqli $db
		 */
		public static $db;

		/**
		 * @var int $insertID
		 */
		public static $insertID;

		/**
		 * @param string $dbHost
		 * @param string $dbUser
		 * @param string $dbPassword
		 * @param string $dbName
		 */
		public static function init( $dbHost = DB_HOST, $dbUser = DB_USER, $dbPassword = DB_PASSWORD, $dbName = DB_DATABASE ) {
			static::$db = new mysqli( $dbHost, $dbUser, $dbPassword, $dbName );
			if( static::$db->connect_errno ) {
				$_SESSION[ 'errors' ][] = 'MySQL connection failed: ' . static::$db->connect_error;
			}
			static::$db->query( 'SET NAMES utf8;' );
		}

		/**
		 * querys sql on database
		 *
		 * @param string $sql sql to query
		 * @param bool   $debug
		 *
		 * @return mixed result set
		 */
		public static function query( $sql, $debug = FALSE ) {
			$result = static::$db->query( $sql );

			static::debug( $sql, $debug );

			static::error( $sql );

			return $result;
		}

		/**
		 * add sql to session sql array for debugging porpuse
		 *
		 * @param      $sql
		 * @param bool $debug
		 */
		protected static function debug( $sql, $debug = FALSE ) {
			if( $debug === TRUE ) {
				$_SESSION[ 'debug' ][] = debug_backtrace()[ 1 ][ 'function' ] . ': $sql is <strong>' . $sql . '</strong>';
			}
		}

		/**
		 * add sql to session error array for error retrace
		 *
		 * @param      $sql
		 */
		protected static function error( $sql ) {
			if( static::$db->errno ) {
				$_SESSION[ 'errors' ][] = '<p>' . debug_backtrace()[ 1 ][ 'function' ] . ' failed: ' . static::$db->error . '<br> statement was: <strong>' . $sql . '</strong></p>';
			}
		}

		/**
		 * insert an entry to the specified table
		 *
		 * @param string $table database table to insert into
		 * @param array  $data  data to insert
		 * @param bool   $debug
		 *
		 * @return mixed
		 */
		public static function insert( $table, $data, $debug = FALSE ) {
			$keys   = [ ];
			$values = [ ];
			foreach( $data as $key => $value ) {
				$value    = static::escape( $value );
				$keys[]   = '`' . static::escape( $key ) . '`';
				$values[] = '\'' . ( $value !== NULL ?: $value ) . '\'';
			}

			$sql = 'INSERT INTO ' . $table . ' (' . implode( ', ', $keys ) . ') VALUES (' . implode( ', ', $values ) . ')';

			static::debug( $sql, $debug );

			static::$db->query( $sql );

			static::error( $sql );

			return static::$insertID = static::$db->insert_id;
		}

		/**
		 * escape given string
		 *
		 * @param  string $string string to escape
		 *
		 * @return string             escaped string
		 */
		public static function escape( $string ) {
			return static::$db->real_escape_string( $string );
		}

		/**
		 * update an entry in specified table
		 *
		 * @param string $table database table to update
		 * @param string $id    id of dataset to update
		 * @param array  $data  updated data
		 * @param bool   $debug
		 */
		public static function update( $table, $id, $data, $debug = FALSE ) {
			$sql = 'UPDATE ' . $table . ' SET ';

			foreach( $data as $key => $value ) {
				$key   = static::escape( $key );
				$value = static::escape( $value );
				if( $value === NULL ) {
					$sql .= '`' . $key . '` = null, ';
				} else {
					$sql .= '`' . $key . '` = \'' . $value . '\', ';
				}
			}

			$sql = rtrim( $sql, ', ' );

			$sql .= ' WHERE `' . static::getPrimaryKeyColumn( $table ) . '` = \'' . $id . '\'';

			static::debug( $sql, $debug );

			static::$db->query( $sql );

			static::error( $sql );
		}

		/**
		 * get primary key column from given table
		 *
		 * @param  string $table table to get primary key from
		 * @param bool    $debug
		 *
		 * @return string primary key column name
		 */
		public static function getPrimaryKeyColumn( $table, $debug = FALSE ) {
			$sql = 'SHOW KEYS FROM ' . $table . ' WHERE `key_name` = \'PRIMARY\'';

			static::debug( $sql, $debug );

			$result = static::$db->query( $sql );

			if( $row = $result->fetch_assoc() ) {
				return $row[ 'Column_name' ];
			}

			static::error( $sql );

			return FALSE;
		}

		/**
		 * select data from specified table
		 *
		 * @param string       $table   database table to select from
		 * @param array|string $columns colums to select, default all
		 * @param array        $where   where condition
		 * @param string       $limit   limit
		 * @param string       $opt     GROUB BY or ORDER BY | Use this if you have no where clause
		 * @param bool         $debug
		 *
		 * @return array fetched data
		 */
		public static function select( $table, $columns = '*', $where = NULL, $limit = NULL, $opt = NULL, $debug = FALSE ) {
			$sql = 'SELECT ' . static::generateColumnList( $columns ) . " FROM $table";
			if( $where !== NULL ) {
				$sql .= ' WHERE ' . $where;
			}
			if( $opt !== NULL ) {
				$sql .= ' ' . $opt;
			}
			if( $limit !== NULL ) {
				$sql .= ' LIMIT ' . $limit;
			}

			static::debug( $sql, $debug );

			$result = static::$db->query( $sql );
			if( static::$db->errno ) {
				static::error( $sql );

				return [ ];
			} else {
				$ret = [ ];
				while( $row = $result->fetch_array() ) {
					$ret[] = $row;
				}
				if( count( $ret ) === 1 ) {
					return $ret;
				} else {
					return $ret;
				}
			}
		}

		/**
		 * generates list of columns from array
		 *
		 * @param  array $columns array of columns
		 *
		 * @return string                  imploded array
		 */
		private static function generateColumnList( $columns ) {
			if( is_array( $columns ) ) {
				return implode( ', ', $columns );
			} else {
				return $columns;
			}

		}

		/**
		 * delete data from specified table
		 *
		 * @param string $table database table to delete from
		 * @param string $id    id of dataset to delete
		 * @param string $col
		 * @param bool   $debug
		 *
		 * @return bool
		 */
		public static function delete( $table, $id, $col = NULL, $debug = FALSE ) {
			if( $col === NULL ) {
				$col = static::getPrimaryKeyColumn( $table );
			}

			$sql = 'DELETE FROM ' . $table . ' WHERE ' . $col . ' = ' . $id;

			static::debug( $sql, $debug );

			static::$db->query( $sql );

			if( static::$db->errno ) {
				static::error( $sql );

				return FALSE;
			} else {
				return TRUE;
			}
		}
	}