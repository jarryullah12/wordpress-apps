<?php

namespace Leadin\admin\api;

use Leadin\api\Base_Api_Controller;
use Leadin\admin\Connection;

/**
 * Portal Api, used to clean portal id and domain from the WordPress options.
 */
class Portal_Api_Controller extends Base_Api_Controller {

	/**
	 * Class constructor, register route.
	 */
	public function __construct() {
		self::register_leadin_admin_route(
			'/portal',
			\WP_REST_Server::DELETABLE,
			array( $this, 'disconnect_portal' )
		);
	}

	/**
	 * Removes portal id and domain from the WordPress options.
	 */
	public function disconnect_portal() {
		if ( Connection::is_connected() ) {
			Connection::disconnect();
			return new \WP_REST_Response( 'OK', 200 );
		}
		return new \WP_REST_Response( 'No leadin_portal_id found, cannot disconnect', 400 );
	}

}
