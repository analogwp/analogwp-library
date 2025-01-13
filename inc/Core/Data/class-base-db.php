<?php
/**
 * Base library database.
 *
 * @package AnalogWP\CustomLibrary
 */

namespace AnalogWP\CustomLibrary\Core\Data;

/**
 * Class Base_DB.
 */
abstract class Base_DB {

	/**
	 * Table name.
	 *
	 * @var string
	 */
	public $table_name;

	/**
	 * DB Version.
	 *
	 * @var string
	 */
	public $version;

	/**
	 * Primary key.
	 *
	 * @var string
	 */
	public $primary_key;

	/**
	 * Class constructor.
	 *
	 * @return void
	 */
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
	 * @param int $row_id Primary key.
	 *
	 * @return  object
	 */
	public function get( $row_id ) {
		global $wpdb;

		// Sanitize the table name.
		$table_name = esc_sql( $this->table_name );
		$row_id     = esc_sql( $row_id );

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE $this->primary_key = %s LIMIT 1;", $row_id ) ); // phpcs:ignore.
	}

	/**
	 * Insert a new row
	 *
	 * @param array  $data Template data.
	 * @param string $type Action type.
	 *
	 * @return  int
	 */
	public function insert( $data, $type = '' ) {
		global $wpdb;

		// Set default values.
		$data = wp_parse_args( $data, $this->get_column_defaults() );

		do_action( 'analog_custom_library_pre_insert_' . $type, $data );

		// Initialise column format array.
		$column_formats = $this->get_columns();

		// Force fields to lower case.
		$data = array_change_key_case( $data );

		// White list columns.
		$data = array_intersect_key( $data, $column_formats );

		// Reorder $column_formats to match the order of columns given in $data.
		$data_keys      = array_keys( $data );
		$column_formats = array_merge( array_flip( $data_keys ), $column_formats );

		$wpdb->insert( $this->table_name, $data, $column_formats ); // phpcs:ignore.

		$wpdb_insert_id = $wpdb->insert_id;

		do_action( 'analog_custom_library_post_insert_' . $type, $wpdb_insert_id, $data );

		if ( $wpdb_insert_id ) {
			$template_id = intval( $data['template_id'] );
			$this->clear_template_cache( $template_id );
		}

		return $wpdb_insert_id;
	}

	/**
	 * Update a row
	 *
	 * @param int    $row_id Row id.
	 * @param array  $data Template data.
	 * @param string $where Primary key.
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

		$query_result = $wpdb->update( $this->table_name, $data, array( $where => $row_id ), $column_formats ); // phpcs:ignore.

		if ( false === $query_result ) {
			return false;
		}

		$template_id = intval( $data['template_id'] );

		// Clear cache after updating.
		$this->clear_template_cache( $template_id );

		return true;
	}

	/**
	 * Delete a row identified by the primary key
	 *
	 * @param int $row_id Row id.
	 * @param int $post_id Template id.
	 *
	 * @return  bool
	 */
	public function delete( $row_id, $post_id ) {
		global $wpdb;

		// Row ID must be positive integer.
		$row_id = absint( $row_id );

		if ( empty( $row_id ) ) {
			return false;
		}

		$query_result = $wpdb->query( $wpdb->prepare( "DELETE FROM $this->table_name WHERE $this->primary_key = %d", $row_id ) ); // phpcs:ignore

		if ( false === $query_result ) {
			return false;
		}

		$template_id = intval( $post_id );

		// Clear cache after updating.
		$this->clear_template_cache( $template_id );

		return true;
	}

	/**
	 * Check if the given table exists
	 *
	 * @param  string $table_name The table name.
	 * @return bool          If the table name exists
	 */
	public function table_exists( $table_name ) {
		global $wpdb;

		$table_name = esc_sql( $table_name );

		return $table_name === $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ); // phpcs:ignore.
	}

	/**
	 * Check if the table was ever installed
	 *
	 * @return bool Returns if the customers table was installed and upgrade routine run
	 */
	public function installed() {
		return $this->table_exists( $this->table_name );
	}

	/**
	 * Clear the cache for a specific template.
	 *
	 * @param int $template_id Template ID.
	 */
	public function clear_template_cache( $template_id ) {
		$cache_keys = array(
			"analog_custom_library_template_exists_{$template_id}", // Template exists check.
			"analog_custom_library_template_content_{$template_id}", // Per template content.
			'analog_custom_library_all_templates', // All templates cache.
		);

		// Loop over the cache keys, deleting them one by one.
		foreach ( $cache_keys as $cache_key ) {
			wp_cache_delete( $cache_key, 'plugin_cache' );
		}
	}
}
