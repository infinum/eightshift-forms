<?php

class WP_REST_Request
{
	/**
	 * Constructor.
	 *
	 * @since 4.4.0
	 *
	 * @param string $method     Optional. Request method. Default empty.
	 * @param string $route      Optional. Request route. Default empty.
	 * @param array  $attributes Optional. Request attributes. Default empty array.
	 */
	public function __construct( $method = '', $route = '', $attributes = array() ) {
		$this->params = array(
			'URL'      => array(),
			'GET'      => array(),
			'POST'     => array(),
			'FILES'    => array(),

			// See parse_json_params.
			'JSON'     => null,

			'defaults' => array(),
		);

		$this->method = $method;
    $this->route = $route;
		$this->attributes = $attributes;
  }

	/**
	 * Retrieves parameters from the query string.
	 *
	 * These are the parameters you'd typically find in `$_GET`.
	 *
	 * @since 4.4.0
	 *
	 * @return array Parameter map of key to value
	 */
	public function get_query_params() {
		return $this->params['GET'];
	}
}