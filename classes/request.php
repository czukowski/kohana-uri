<?php defined('SYSPATH') or die('No direct script access.');
/**
 * A very shallow integration of Request with URI class
 */
class Request extends Kohana_Request
{
	/**
	 * Generates a relative URI for the current route.
	 *
	 *     $request->uri($params);
	 *
	 * @param   array   $params  Additional route parameters
	 * @return  URI
	 */
	public function uri(array $params = NULL)
	{
		return new URI(parent::uri($params));
	}

	/**
	 * Create a URL from the current request. This is a shortcut for:
	 *
	 *     echo URL::site($this->request->uri($params), $protocol);
	 *
	 * @param   array    $params    URI parameters
	 * @param   mixed    $protocol  protocol string or Request object
	 * @return  URI
	 * @since   3.0.7
	 */
	public function url(array $params = NULL, $protocol = NULL)
	{
		return new URI(parent::url($params, $protocol));
	}
}