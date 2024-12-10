<?php
/**
 * Library templates database.
 *
 * @package AnalogWP\CustomLibrary
 */

namespace AnalogWP\CustomLibrary\Core\Data;

/**
 * Class Templates_DB.
 */
class Templates_DB extends Base_DB {
	/**
	 * The name of the cache group.
	 *
	 * @var string
	 */
	public $cache_group = 'analog_custom_library_custom_sync_templates';

	/**
	 * Templates_DB constructor.
	 */
	public function __construct() {
		parent::__construct();

		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'analog_custom_templates';
		$this->primary_key = 'id';
		$this->version     = '1.0';

		if ( ! $this->installed() ) {
			$this->create_table();
		}
	}

	/**
	 * Get Table columns and formats.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'id'          => '%d',
			'template_id' => '%d',
			'site_id'     => '%d',
			'installs'    => '%d',
			'title'       => '%s',
			'meta'        => '%s',
			'content'     => '%s',
			'created_at'  => '%s',
			'updated_at'  => '%s',
		);
	}

	/**
	 * Get default column values.
	 *
	 * @return array
	 */
	public function get_column_defaults() {
		return array(
			'template_id' => 0,
			'site_id'     => 0,
			'installs'    => 0,
			'title'       => null,
			'meta'        => null,
			'content'     => null,
			'created_at'  => gmdate( 'Y-m-d H:i:s' ),
			'updated_at'  => gmdate( 'Y-m-d H:i:s' ),
		);
	}

	/**
	 * Creates a custom table using table name from $this->table_name.
	 *
	 * @return void
	 */
	public function create_table() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Validate table name to prevent SQL injection.
		$table_name = esc_sql( $this->table_name );

		$sql = "CREATE TABLE {$table_name} (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		template_id bigint(20) NOT NULL,
		site_id bigint(20) NOT NULL,
		installs bigint(20),
		title text NOT NULL,
		meta longtext NOT NULL,
		content longtext NOT NULL,
		created_at datetime NOT NULL,
		updated_at datetime NOT NULL,
		PRIMARY KEY  (id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}

	/**
	 * Check if template exists.
	 *
	 * @param int $template_id Template ID.
	 * @param int $site_id Site ID.
	 * @return array|object|\stdClass|null
	 */
	public function template_exists( $template_id, $site_id = 0 ) {
		global $wpdb;

		// Validate table name to prevent SQL injection.
		$table_name = esc_sql( $this->table_name );

		// Cache key for storing the query result.
		$cache_key = "analog_custom_library_template_exists_{$template_id}";

		// Attempt to get cached result.
		$cached_result = wp_cache_get( $cache_key, 'plugin_cache' );

		if ( false !== $cached_result ) {
			return $cached_result;
		}

		// Prepare the query without directly interpolating the table name.
		$sql = $wpdb->prepare(
			"SELECT * FROM {$table_name} WHERE template_id = %d AND site_id = %d LIMIT 1", // phpcs:ignore
			$template_id,
			$site_id
		);

		// Execute the query.
		$result = $wpdb->get_row( $sql ); // phpcs:ignore

		// Store the result in the cache.
		wp_cache_set( $cache_key, $result, 'plugin_cache', 3600 );

		return $result;
	}

	/**
	 * Get all templates in table.
	 *
	 * @return array|object|\stdClass[]|null
	 */
	public function get_templates() {
		global $wpdb;

		// Define a unique cache key.
		$cache_key = 'analog_custom_library_all_templates';

		// Attempt to get cached results.
		$cached_results = wp_cache_get( $cache_key, 'plugin_cache' );
		if ( false !== $cached_results ) {
			return $cached_results;
		}

		// Sanitize the table name.
		$table_name = esc_sql( $this->table_name );

		// Build the query.
		$query = "SELECT template_id, site_id, installs, title, meta FROM {$table_name} ORDER BY created_at DESC"; // phpcs:ignore

		// Execute the query.
		$results = $wpdb->get_results( $query ); // phpcs:ignore

		// Store the results in the cache for 1 hour.
		wp_cache_set( $cache_key, $results, 'plugin_cache', 3600 );

		return $results;
	}

	/**
	 * Get template content by id.
	 *
	 * @param int $template_id Template ID.
	 * @param int $site_id Site ID.
	 * @return array|object|\stdClass|null
	 */
	public function get_template_content( $template_id, $site_id = 0 ) {
		global $wpdb;

		// Define a unique cache key.
		$cache_key = "analog_custom_library_template_content_{$template_id}";

		// Attempt to get cached results.
		$cached_result = wp_cache_get( $cache_key, 'plugin_cache' );
		if ( false !== $cached_result ) {
			return $cached_result;
		}

		// Sanitize the table name.
		$table_name = esc_sql( $this->table_name );

		// Build and execute the query.
		$query = $wpdb->prepare(
			"SELECT meta, content FROM {$table_name} WHERE template_id = %d AND site_id = %d", // phpcs:ignore
			$template_id,
			$site_id
		);

		// Execute query.
		$result = $wpdb->get_row( $query ); // phpcs:ignore

		// Store the result in the cache for 1 hour.
		wp_cache_set( $cache_key, $result, 'plugin_cache', 3600 );

		return $result;
	}
}
