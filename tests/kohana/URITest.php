<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests URI
 *
 * @group kohana
 * @group kohana.uri
 *
 * @package    Kohana
 * @category   Tests
 * @author     Korney Czukowski
 * @license    http://kohanaframework.org/license
 */
class Kohana_URITest extends Unittest_TestCase
{
	/**
	 * @return array
	 */
	public function provider_uri()
	{
		return array(
			array(
				'ftp://example.com/kohana/index.php/',
				array(
					'uri' => 'ftp://example.com/kohana/index.php/',
					'scheme' => 'ftp',
					'user' => NULL,
					'pass' => NULL,
					'host' => 'example.com',
					'port' => NULL,
					'path' => '/kohana/index.php/',
					'query' => NULL,
					'fragment' => NULL,
				),
			),
			array(
				'http://username:password@example.com:8080/',
				array(
					'uri' => 'http://username:password@example.com:8080/',
					'scheme' => 'http',
					'user' => 'username',
					'pass' => 'password',
					'host' => 'example.com',
					'port' => '8080',
					'path' => '/',
					'query' => NULL,
					'fragment' => NULL,
				),
			),
			array(
				'http://example.com#blah',
				array(
					'uri' => 'http://example.com#blah',
					'scheme' => 'http',
					'user' => NULL,
					'pass' => NULL,
					'host' => 'example.com',
					'port' => NULL,
					'path' => NULL,
					'query' => NULL,
					'fragment' => 'blah',
				),
			),
			array(
				'ftp://example.com/kohana/index.php/route/goes/here?kohana=awesome&life=good#fact',
				array(
					'uri' => 'ftp://example.com/kohana/index.php/route/goes/here?kohana=awesome&life=good#fact',
					'scheme' => 'ftp',
					'user' => NULL,
					'pass' => NULL,
					'host' => 'example.com',
					'port' => NULL,
					'path' => '/kohana/index.php/route/goes/here',
					'query' => array(
						'kohana' => 'awesome',
						'life' => 'good',
					),
					'fragment' => 'fact',
				),
			),
		);
	}

	/**
	 * @dataProvider provider_uri
	 */
	public function test_parse($uri, $expected)
	{
		$parsed = URI::parse($uri);
	//	echo Debug::vars($parsed, $expected);
		$this->assertSame($expected, $parsed);
	}

	/**
	 * @dataProvider provider_uri
	 */
	public function test_render($expected, $uri)
	{
		$rendered = URI::render($uri);
		$this->assertSame($expected, $rendered);
	}

	/**
	 * Tests URI creation from Route object
	 * @test
	 */
	public function test_get_set()
	{
		// Set up test route
		$route = new Route('<action>(/<preview>)/<id>/<filename>',
			array(
				'action' => 'download|preview',
				'id' => '\d+',
				'filename' => '[\pN\pL\'~`!@$\#%&.()+ _-]+',
				'preview' => '\w+',
			));
		$route->defaults(array(
			'action' => 'download',
		));
		$uri = URI::factory($route, array(
			'id' => '123',
			'filename' => 'file.txt',
		));

		$this->assertEquals( (string) $uri, 'download/123/file.txt');

		$uri->set('host', 'example.com');
		$this->assertEquals( (string) $uri, 'example.com/download/123/file.txt');

		$uri->set('scheme', 'https');
		$this->assertEquals( (string) $uri, 'https://example.com/download/123/file.txt');

		$uri->set('port', '8080');
		$this->assertEquals( (string) $uri, 'https://example.com:8080/download/123/file.txt');

		$uri->erase('port');
		$this->assertEquals( (string) $uri, 'https://example.com/download/123/file.txt');

		$uri->set('query', array('foo' => 'bar'));
		$this->assertEquals( (string) $uri, 'https://example.com/download/123/file.txt?foo=bar');

		$uri->set('query', 'boo', 'far');
		$this->assertEquals( (string) $uri, 'https://example.com/download/123/file.txt?foo=bar&boo=far');

		$uri->set('query', array('foo' => 'bar'));
		$this->assertEquals( (string) $uri, 'https://example.com/download/123/file.txt?foo=bar');
	}
}
