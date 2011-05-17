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

	public function bench_parse_regex($uri)
	{
		return URI::parse($uri);
	}

	public function bench_parse_url($uri)
	{
		return URI::parse_url($uri);
	}
}