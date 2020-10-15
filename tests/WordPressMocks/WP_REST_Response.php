<?php

class WP_REST_Response
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
	public function __construct( $data ) {
		$this->data = $data;
  }
}