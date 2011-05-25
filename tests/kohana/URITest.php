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
	protected $_part_names = array('uri', 'scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment');

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
				array(
					'absolute' => '/kohana/index.php/',
					'base' => '/kohana/index.php/',
					'is_absolute' => TRUE,
					'relative' => '',
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
				array(
					'absolute' => '/',
					'base' => '/',
					'is_absolute' => TRUE,
					'relative' => '',
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
				array(
					'absolute' => '/',
					'base' => '/',
					'is_absolute' => TRUE,
					'relative' => '',
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
				array(
					'absolute' => '/kohana/index.php/route/goes/here',
					'base' => '/kohana/index.php/',
					'is_absolute' => TRUE,
					'relative' => 'route/goes/here',
				),
			),
			array(
				'/kohana/index.php/my/site/page:5',
				array(
					'uri' => '/kohana/index.php/my/site/page:5',
					'scheme' => NULL,
					'user' => NULL,
					'pass' => NULL,
					'host' => NULL,
					'port' => NULL,
					'path' => '/kohana/index.php/my/site/page:5',
					'query' => NULL,
					'fragment' => NULL,
				),
				array(
					'absolute' => '/kohana/index.php/my/site/page:5',
					'base' => '/kohana/index.php/',
					'is_absolute' => TRUE,
					'relative' => 'my/site/page:5',
				),
			),
			array(
				'my/site/page:5',
				array(
					'uri' => 'my/site/page:5',
					'scheme' => NULL,
					'user' => NULL,
					'pass' => NULL,
					'host' => NULL,
					'port' => NULL,
					'path' => 'my/site/page:5',
					'query' => NULL,
					'fragment' => NULL,
				),
				array(
					'absolute' => '/kohana/index.php/my/site/page:5',
					'base' => '/kohana/index.php/',
					'is_absolute' => FALSE,
					'relative' => 'my/site/page:5',
				),
			),
			array(
				'http://example.com/kohana/index.php/my/site/page:5',
				array(
					'uri' => 'http://example.com/kohana/index.php/my/site/page:5',
					'scheme' => 'http',
					'user' => NULL,
					'pass' => NULL,
					'host' => 'example.com',
					'port' => NULL,
					'path' => '/kohana/index.php/my/site/page:5',
					'query' => NULL,
					'fragment' => NULL,
				),
				array(
					'absolute' => '/kohana/index.php/my/site/page:5',
					'base' => '/kohana/index.php/',
					'is_absolute' => TRUE,
					'relative' => 'my/site/page:5',
				),
			),
		);
	}

	/**
	 * @dataProvider provider_uri
	 */
	public function test_get($uri, $parts)
	{
		$instance = new URI($uri);
		$this->assertSame($instance->get(), $parts);
		foreach ($this->_part_names as $part)
		{
			$instance = new URI($uri);
			$this->assertEquals($instance->get($part), $parts[$part]);
		}
	}

	/**
	 * @dataProvider provider_uri
	 */
	public function test_copy($uri)
	{
		$original = new URI($uri);
		$copy = $original->copy();
		$this->assertNotSame($original, $copy);
		foreach ($this->_part_names as $part)
		{
			$this->assertEquals($original->get($part), $copy->get($part));
		}
	}

	/**
	 * @dataProvider provider_uri
	 */
	public function test_parse($uri, $expected)
	{
		$parsed = URI::parse($uri);
		$this->assertSame($expected, $parsed);
	}

	/**
	 * @dataProvider provider_uri
	 */
	public function test_render($expected, $uri, $array)
	{
		$rendered = URI::render($uri, $array['is_absolute']);
		$this->assertSame($expected, $rendered);
	}

	/**
	 * Tests URI creation from Route object
	 * @test
	 */
	public function test_manipulations()
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
		$uri = URI::factory($route->uri(array(
			'id' => '123',
			'filename' => 'file.txt',
		)));

		$this->assertEquals( (string) $uri, 'download/123/file.txt');
		$this->assertFalse($uri->is_absolute());
		$this->assertTrue($uri->is_relative());

		$uri->set('host', 'example.com');
		$this->assertEquals( (string) $uri, 'example.com/download/123/file.txt');
		$this->assertTrue($uri->is_absolute());
		$this->assertFalse($uri->is_relative());

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

		$uri->to_relative('/');
		$this->assertEquals($uri->get('path'), 'download/123/file.txt');
		$this->assertEquals( (string) $uri, 'download/123/file.txt?foo=bar');

	}

	/**
	 * Test absolute URI detection
	 * 
	 * @dataProvider provider_uri
	 */
	public function test_is_absolute($string, $parts, $array)
	{
		$this->assertEquals(URI::factory($string)->is_absolute(), $array['is_absolute']);
		$this->assertEquals(URI::factory($parts)->is_absolute(), $array['is_absolute']);
	}

	/**
	 * Test convert URI to absolute/relative and back
	 * 
	 * @dataProvider provider_uri
	 */
	public function test_absolute_relative($string, $parts, $array)
	{
		$uri = URI::factory($string);
		$base_url = $array['base'];

		for ($i = 0; $i < 2; $i++)
		{
			if ($uri->is_absolute())
			{
				$uri->to_relative($base_url);
				$this->assertEquals($uri->get('path'), $array['relative']);
			}
			else
			{
				$uri->to_absolute($base_url);
				$this->assertEquals($uri->get('path'), $array['absolute']);
			}
		}
	}
}
