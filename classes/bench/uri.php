<?php defined('SYSPATH') or die('No direct script access.');

class Bench_URI extends Codebench
{
	public $description = 'URI parse benchmark.';

	public $subjects = array(
		'/kohana/',
		'http://example.com/kohana/',
		'/kohana/index.php/',
		'http://example.com/kohana/index.php/',
		'https://example.com/kohana/index.php/',
		'ftp://example.com/kohana/index.php/',
		'cli://example.com/kohana/index.php/',
		'https://example.com/kohana/',
		'http://example.com:8080/',
		'http://www.example.com/',
		'/kohana/index.php/my/site',
		'http://example.com/kohana/index.php/my/site',
		//'/kohana/index.php/my/site/page:5',
		'http://example.com/kohana/index.php/my/site/page:5',
		'/kohana/index.php/my/site?var=asd&kohana=awesome',
		'http://example.com/kohana/index.php/my/site?var=asd&kohana=awesome',
		'/kohana/index.php/?kohana=awesome&life=good',
		'http://example.com/kohana/index.php/?kohana=awesome&life=good',
		'/kohana/index.php/?kohana=awesome&life=good#fact',
		'http://example.com/kohana/index.php/?kohana=awesome&life=good#fact',
		'/kohana/index.php/some/long/route/goes/here?kohana=awesome&life=good#fact',
		'http://example.com/kohana/index.php/some/long/route/goes/here?kohana=awesome&life=good#fact',
		'https://example.com/kohana/index.php/route/goes/here?kohana=awesome&life=good#fact',
		'ftp://example.com/kohana/index.php/route/goes/here?kohana=awesome&life=good#fact',
	);

	protected static $_part_names = array('uri', 'scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment');

	public function bench_parse_regex($uri)
	{
		// Parse URI string
		preg_match('/^(?:(\w+):)?(?:\/\/(?:(?:([^:@\/]*):?([^:@\/]*))?@)?([^:\/?#]*)(?::(\d*))?)?([^?#]*)(?:\?([^#]*))?(?:#(.*))?/', $uri, $parts);

		// Combine matches with names to get associative array
		$parts = array_combine(self::$_part_names, array_pad($parts, count(self::$_part_names), NULL));

		// Replace empty matches with NULL values
		foreach ($parts as $part => $value)
		{
			if ($value === '')
			{
				$parts[$part] = NULL;
			}
		}

		// Parse query string
		if ($parts['query'] !== NULL)
		{
			parse_str($parts['query'], $parts['query']);
		}

		return $parts;
	}

	public function bench_parse_url($uri)
	{
		$parts = parse_url($uri);

		// Combine matches with names to get associative array
		$parts = array_merge(array_combine(self::$_part_names, array_pad(array(), count(self::$_part_names), NULL)), $parts);

		// Replace empty matches with NULL values
		foreach ($parts as $part => $value)
		{
			if ($value === '')
			{
				$parts[$part] = ($part === 'path' ? '/' : NULL);
			}
		}

		// Parse query string
		if ($parts['query'] !== NULL)
		{
			parse_str($parts['query'], $parts['query']);
		}

		// Parse query string
		if ($parts['port'] !== NULL)
		{
			$parts['port'] = (string) $parts['port'];
		}

		$parts['uri'] = $uri;

		return $parts;
	}
}