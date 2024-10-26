<?php

namespace Analog\Slink\Data;


class Templates_DB extends Base_DB {
	/**
	 * The name of the cache group.
	 *
	 * @var string
	 */
	public $cache_group = 'ang_preview_sync_patterns';

	/**
	 * DB_Patterns constructor.
	 */
	function __construct() {
		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'slink_self_templates';
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
			'created_at'  => date( 'Y-m-d H:i:s' ),
			'updated_at'  => date( 'Y-m-d H:i:s' ),
		);
	}

	public function create_table() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$sql = "CREATE TABLE {$this->table_name} (
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

	public function insert( $data, $type = '' ) {
		$result = parent::insert( $data, $type );

		if ( $result ) {
			$this->set_last_changed();
		}

		return $result;
	}

	/**
	 * Sets the last_changed cache key for API requests.
	 */
	public function set_last_changed() {
		wp_cache_set( 'last_changed', microtime(), $this->cache_group );
	}

	/**
	 * Retrieves the value of the last_changed cache key for API requests..
	 */
	public function get_last_changed() {
		if ( function_exists( 'wp_cache_get_last_changed' ) ) {
			return wp_cache_get_last_changed( $this->cache_group );
		}

		$last_changed = wp_cache_get( 'last_changed', $this->cache_group );
		if ( ! $last_changed ) {
			$last_changed = microtime();
			wp_cache_set( 'last_changed', $last_changed, $this->cache_group );
		}

		return $last_changed;
	}

	public function pattern_exists( $post_id, $site_id ) {
		global $wpdb;

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE template_id = %d AND site_id = %d LIMIT 1;", $post_id, $site_id ) );
	}

	public function get_patterns() {
		global $wpdb;

		$results = $wpdb->get_results( "SELECT template_id, site_id, installs, title, meta FROM $this->table_name ORDER BY created_at DESC" );

		return $results;
	}

	public function get_pattern_content( $template_id, $site_id ) {
		global $wpdb;

		$result = $wpdb->get_row( $wpdb->prepare( "SELECT meta, content FROM $this->table_name WHERE template_id = %d AND site_id = %d", $template_id, $site_id ) );

		return $result;
	}
}
