<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Unit tests for request class
 *
 * @group kohana
 * @group kohana.request
 * @group kohana.request.uri
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @author     BRMatt <matthew@sigswitch.com>
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_RequestTestURI extends Unittest_TestCase
{
	protected function _get_inject_route()
	{
		$route = new Route('(<controller>(/<action>(/<id>)))');
		$route->defaults(array(
			'controller' => 'welcome',
			'action'     => 'index',
		));

		return $route;
	}

	/**
	 * Ensure that parameters can be read
	 *
	 * @test
	 */
	public function test_param()
	{
		$uri = 'foo/bar/id';
		$request = Request::factory($uri, NULL, array($this->_get_inject_route()));

		$this->assertArrayHasKey('id', $request->param());
		$this->assertArrayNotHasKey('foo', $request->param());
		$this->assertEquals( (string) $request->uri(), $uri);

		// Ensure the params do not contain contamination from controller, action, route, uri etc etc
		$params = $request->param();

		// Test for illegal components
		$this->assertArrayNotHasKey('controller', $params);
		$this->assertArrayNotHasKey('action', $params);
		$this->assertArrayNotHasKey('directory', $params);
		$this->assertArrayNotHasKey('uri', $params);
		$this->assertArrayNotHasKey('route', $params);

		$route = new Route('(<uri>)', array('uri' => '.+'));
		$route->defaults(array('controller' => 'foobar', 'action' => 'index'));
		$request = Request::factory('foobar', NULL, array($route));

		$this->assertSame('foobar', $request->param('uri'));
	}

	/**
	 * Provides test data for Request::url()
	 * @return array
	 */
	public function provider_url()
	{
		return array(
			array(
				'foo/bar',
				array(),
				'http',
				TRUE,
				'http://localhost/kohana/foo/bar'
			),
			array(
				'foo',
				array('action' => 'bar'),
				'http',
				TRUE,
				'http://localhost/kohana/foo/bar'
			),
		);
	}

	/**
	 * Tests Request::url()
	 *
	 * @test
	 * @dataProvider provider_url
	 * @covers Request::url
	 * @param string $route the route to use
	 * @param array $params params to pass to route::uri
	 * @param string $protocol the protocol to use
	 * @param array $expected The string we expect
	 */
	public function test_url($uri, $params, $protocol, $is_cli, $expected)
	{
		$this->setEnvironment(array(
			'Kohana::$base_url'  => '/kohana/',
			'_SERVER'            => array('HTTP_HOST' => 'localhost', 'argc' => $_SERVER['argc']),
			'Kohana::$index_file' => FALSE,
			'Kohana::$is_cli'    => $is_cli,
		));

		$this->assertEquals( (string) Request::factory($uri, NULL, array($this->_get_inject_route()))->url($params, $protocol), $expected);
	}

	/**
	 * Provides data for test_uri_only_trimed_on_internal()
	 *
	 * @return  array
	 */
	public function provider_uri_only_trimed_on_internal()
	{
		$old_request = Request::$initial;
		Request::$initial = new Request('foo/bar');

		$result = array(
			array(
				new Request('http://www.google.com'),
				'http://www.google.com'
			),
			array(
				new Request('http://www.google.com/'),
				'http://www.google.com/'
			),
			array(
				new Request('foo/bar/'),
				'foo/bar'
			),
			array(
				new Request('foo/bar'),
				'foo/bar'
			)
		);

		Request::$initial = $old_request;
		return $result;
	}

	/**
	 * Tests that the uri supplied to Request is only trimed
	 * for internal requests.
	 * 
	 * @dataProvider provider_uri_only_trimed_on_internal
	 *
	 * @return void
	 */
	public function test_uri_only_trimed_on_internal(Request $request, $expected)
	{
		$this->assertSame( (string) $request->uri(), $expected);
	}

	/**
	 * Provider for test_uri_without_query_parameters
	 *
	 * @return  array
	 */
	public function provider_uri_without_query_parameters()
	{
		return array(
			array(
				new Request('foo/bar?foo=bar&bar=foo'),
				array(),
				'foo/bar'
			),
			array(
				new Request('foo/bar'),
				array('bar' => 'foo', 'foo' => 'bar'),
				'foo/bar'
			),
			array(
				new Request('foo/bar'),
				array(),
				'foo/bar'
			)
		);
	}

	/**
	 * Tests that the [Request::uri()] method does not return
	 * query parameters
	 *
	 * @dataProvider provider_uri_without_query_parameters
	 * 
	 * @param   Request   request 
	 * @param   array     query 
	 * @param   string    expected 
	 * @return  void
	 */
	public function test_uri_without_query_parameters(Request $request, $query, $expected)
	{
		$request->query($query);

		$this->assertSame($expected, (string) $request->uri());
	}
} // End Kohana_RequestTest