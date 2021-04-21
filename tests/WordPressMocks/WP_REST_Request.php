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
	 * @return array Parameter map of key to value
	 */
	public function get_query_params() {
		return $this->method === 'POST' ? $this->params['POST'] : $this->params['GET'];
	}

	/**
	 * Retrieves parameters from body.
	 *
	 * These are the parameters you'd typically find in `$_POST` (if you have both $_GET and $_POST params at the same time)
	 *
	 * @return array Parameter map of key to value
	 */
	public function get_body_params() {
		return $this->params['POST'];
	}

	/**
	 * Retrieves all params merged into 1 array.
	 *
	 * @return array Parameter map of key to value
	 */
	public function get_params() {
		$order = $this->getParameterOrder();
    $order = array_reverse( $order, true );

    $params = array();
    foreach ( $order as $type ) {
        // array_merge() / the "+" operator will mess up
        // numeric keys, so instead do a manual foreach.
        foreach ( (array) $this->params[ $type ] as $key => $value ) {
            $params[ $key ] = $value;
        }
    }

    return $params;
	}

	/**
	 * Retrieves the parameter priority order.
	 *
	 * @return array
	 */
	protected function getParameterOrder() {
		return [
			'JSON',
			'POST',
			'GET',
			'URL',
			'defaults'
		];
	}
}