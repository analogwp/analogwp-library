<?php
/**
 * Base library database.
 *
 * @package Analog Library
 */

namespace Analog\Core\Data;

/**
 * Class Base_DB.
 */
abstract class Base_DB {
	public $table_name;
	public $version;
	public $primary_key;

	public function __construct() {}

	/**
	 * Whitelist of columns
	 *
	 * @return  array
	 */
	public function get_columns() {
		return array();
	}

	/**
	 * Default column values
	 *
	 * @return  array
	 */
	public function get_column_defaults() {
		return array();
	}

	/**
	 * Retrieve a row by the primary key
	 *
	 * @return  object
	 */
	public function get( $row_id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE $this->primary_key = %s LIMIT 1;", $row_id ) );
	}

	/**
	 * Retrieve a row by a specific column / value
	 *
	 * @return  object
	 */
	public function get_by( $column, $row_id ) {
		global $wpdb;
		$column = esc_sql( $column );
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE $column = %s LIMIT 1;", $row_id ) );
	}

	/**
	 * Retrieve a specific column's value by the primary key
	 *
	 * @return  string
	 */
	public function get_column( $column, $row_id ) {
		global $wpdb;
		$column = esc_sql( $column );
		return $wpdb->get_var( $wpdb->prepare( "SELECT $column FROM $this->table_name WHERE $this->primary_key = %s LIMIT 1;", $row_id ) );
	}

	/**
	 * Retrieve a specific column's value by the the specified column / value
	 *
	 * @return  string
	 */
	public function get_column_by( $column, $column_where, $column_value ) {
		global $wpdb;
		$column_where = esc_sql( $column_where );
		$column       = esc_sql( $column );
		return $wpdb->get_var( $wpdb->prepare( "SELECT $column FROM $this->table_name WHERE $column_where = %s LIMIT 1;", $column_value ) );
	}

	/**
	 * Insert a new row
	 *
	 * @return  int
	 */
	public function insert( $data, $type = '' ) {
		global $wpdb;

		// Set default values
		$data = wp_parse_args( $data, $this->get_column_defaults() );

		do_action( 'ang_pre_insert_' . $type, $data );

		// Initialise column format array.
		$column_formats = $this->get_columns();

		// Force fields to lower case.
		$data = array_change_key_case( $data );

		// White list columns.
		$data = array_intersect_key( $data, $column_formats );

		// Reorder $column_formats to match the order of columns given in $data.
		$data_keys      = array_keys( $data );
		$column_formats = array_merge( array_flip( $data_keys ), $column_formats );

		$wpdb->insert( $this->table_name, $data, $column_formats );
		$wpdb_insert_id = $wpdb->insert_id;

		do_action( 'ang_post_insert_' . $type, $wpdb_insert_id, $data );

		return $wpdb_insert_id;
	}

	/**
	 * Update a row
	 *
	 * @return  bool
	 */
	public function update( $row_id, $data = array(), $where = '' ) {

		global $wpdb;

		// Row ID must be positive integer.
		$row_id = absint( $row_id );

		if ( empty( $row_id ) ) {
			return false;
		}

		if ( empty( $where ) ) {
			$where = $this->primary_key;
		}

		// Initialise column format array.
		$column_formats = $this->get_columns();

		// Force fields to lower case.
		$data = array_change_key_case( $data );

		// White list columns.
		$data = array_intersect_key( $data, $column_formats );

		// Reorder $column_formats to match the order of columns given in $data.
		$data_keys      = array_keys( $data );
		$column_formats = array_merge( array_flip( $data_keys ), $column_formats );

		if ( false === $wpdb->update( $this->table_name, $data, array( $where => $row_id ), $column_formats ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Delete a row identified by the primary key
	 *
	 * @return  bool
	 */
	public function delete( $row_id = 0 ) {

		global $wpdb;

		// Row ID must be positive integer.
		$row_id = absint( $row_id );

		if ( empty( $row_id ) ) {
			return false;
		}

		if ( false === $wpdb->query( $wpdb->prepare( "DELETE FROM $this->table_name WHERE $this->primary_key = %d", $row_id ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if the given table exists
	 *
	 * @param  string $table The table name
	 * @return bool          If the table name exists
	 */
	public function table_exists( $table ) {
		global $wpdb;
		$table = sanitize_text_field( $table );

		return $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE '%s'", $table ) ) === $table;
	}

	/**
	 * Check if the table was ever installed
	 *
	 * @return bool Returns if the customers table was installed and upgrade routine run
	 */
	public function installed() {
		return $this->table_exists( $this->table_name );
	}

	public function is_local_url( $url = '' ) {
		$is_local_url = false;

		// Trim it up.
		$url = strtolower( trim( $url ) );

		// Need to get the host...so let's add the scheme so we can use parse_url.
		if ( false === strpos( $url, 'http://' ) && false === strpos( $url, 'https://' ) ) {
			$url = 'http://' . $url;
		}

		$url_parts = wp_parse_url( $url );
		$host      = ! empty( $url_parts['host'] ) ? $url_parts['host'] : false;

		if ( ! empty( $url ) && ! empty( $host ) ) {
			if ( false !== ip2long( $host ) ) {
				if ( ! filter_var( $host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
					$is_local_url = true;
				}
			} elseif ( 'localhost' === $host ) {
				$is_local_url = true;
			}

			$check_tlds = apply_filters( 'ang_validate_tlds', true );
			if ( $check_tlds ) {
				$tlds_to_check = apply_filters(
					'edd_sl_url_tlds',
					array(
						'.local',
						'.test',
					)
				);

				foreach ( $tlds_to_check as $tld ) {
					if ( false !== strpos( $host, $tld ) ) {
						$is_local_url = true;
						continue;
					}
				}
			}

			if ( substr_count( $host, '.' ) > 1 ) {
				$subdomains_to_check = apply_filters(
					'ang_url_subdomains',
					array(
						'dev.',
						'*.staging.',
					)
				);

				foreach ( $subdomains_to_check as $subdomain ) {

					$subdomain = str_replace( '.', '(.)', $subdomain );
					$subdomain = str_replace( array( '*', '(.)' ), '(.*)', $subdomain );

					if ( preg_match( '/^(' . $subdomain . ')/', $host ) ) {
						$is_local_url = true;
						continue;
					}
				}
			}
		}

		return apply_filters( 'ang_is_local_url', $is_local_url, $url );
	}

	/**
	 * Lowercases site URL's, strips HTTP protocols and strips www subdomains.
	 *
	 * @param string $url Site URL to cleanup.
	 * @return string
	 */
	public function clean_site_url( $url ) {
		$url = strtolower( $url );

		if ( apply_filters( 'ang_strip_www', true ) ) {
			// strip www subdomain.
			$url = str_replace( array( '://www.', ':/www.' ), '://', $url );
		}

		if ( apply_filters( 'ang_strip_protocol', true ) ) {
			// strip protocol.
			$url = str_replace( array( 'http://', 'https://', 'http:/', 'https:/' ), '', $url );
		}

		if ( apply_filters( 'ang_strip_port_number', true ) ) {
			$port = wp_parse_url( $url, PHP_URL_PORT );
			if ( $port ) {
				// strip port number.
				$url = str_replace( ':' . $port, '', $url );
			}
		}

		return sanitize_text_field( $url );
	}
}
