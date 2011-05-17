<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana_URI class
 * 
 * @package    Kohana
 * @author     Korney Czukowski
 * @license    http://kohanaframework.org/license
 */
class Kohana_URI
{
	/**
	 * @var array   stores URI parts, when parsed
	 */
	protected $_parts = array();
	/**
	 * @var string  stores raw URI, if available, until parsed
	 */
	protected $_raw_uri = NULL;
	/**
	 * @var bool    flag
	 */
	protected $_parse_flag = FALSE;

	/**
	 * Class constructor
	 * 
	 * @param mixed $uri
	 */
	public function __construct($uri = NULL)
	{
		if (is_array($uri))
		{
			$this->_parts = $uri;
		}
		else
		{
			// $uri parameter is a string, save it as is.
			$this->_raw_uri = $uri;
		}
	}

	/**
	 * Implicit conversion to string
	 * 
	 * @return string
	 */
	public function __toString()
	{
		if ($this->_parse_flag === TRUE)
		{
			$this->_parse_parts();
		}
		elseif ($this->_raw_uri !== NULL)
		{
			// Raw URI has been set and was not altered, return it
			return $this->_raw_uri;
		}

		if ($this->_parts['uri'] === NULL)
		{
			// There's no valid cached string representation, must render it
			$this->_parts['uri'] = URI::render($this->_parts);
		}
		// URI has been just rendered OR parsed, but not altered
		return $this->_parts['uri'];
	}

	/**
	 * URI parts eraser.
	 *
	 * @param   string  $part
	 * @param   mixed   $key
	 * @return  URI
	 */
	public function erase($part, $key = NULL)
	{
		// XXX: should we add a check to prevent invalid part names?
		// i.e. `in_array($part, URI::$_part_names)`

		$this->_parse_parts();
		if ($part === 'query' AND $key !== NULL)
		{
			// Unset query parameter
			unset($this->_parts[$part][$key]);
		}
		else
		{
			// Set URI part to NULL
			$this->_parts[$part] = NULL;
		}
		// Reset raw URI and 'uri' part
		$this->_parts['uri'] = NULL;

		return $this;
	}

	/**
	 * URI parts getter.
	 *
	 * @param   string  $part   URI part to get
	 * @param   mixed   $param  Key or key value pairs to get
	 * @return  mixed
	 */
	public function get($part, $key = NULL)
	{
		// XXX: should we add a check to prevent invalid part names?
		// i.e. `in_array($part, URI::$_part_names)`

		// Parse raw URI, if requested part was not set afterwards, not necessary to do this otherwise
		if ( ! isset($this->_parts[$part]) OR $this->_parts[$part] === NULL)
		{
			$this->_parse_parts();
		}

		if ($part === 'query' AND $key !== NULL)
		{
			// Return single query key
			return Arr::get($this->_parts[$part], $key);
		}
		else
		{
			// Return URI part
			return Arr::get($this->_parts, $part);
		}
	}

	/**
	 * URI parts setter.
	 *
	 * @param   string  $part   URI part to set
	 * @param   mixed   $key    Key or key value pairs to set
	 * @param   mixed   $value  Value to set to a key
	 * @return  URI
	 */
	public function set($part, $key = NULL, $value = NULL)
	{
		// XXX: should we add a check to prevent invalid part names?
		// i.e. `in_array($part, URI::$_part_names)`

		if ($part === 'query' AND ! is_array($key))
		{
			// Set a single query parameter
			$this->_parts[$part][$key] = $value;
		}
		else
		{
			// Set all query parameters or any other data
			$this->_parts[$part] = $key;
		}
		// Reset 'uri' part and set parse flag
		$this->_parts['uri'] = NULL;
		$this->_parse_flag = TRUE;

		return $this;
	}

	/**
	 * Parses URI and resets raw data
	 */
	protected function _parse_parts()
	{
		if ($this->_raw_uri !== NULL)
		{
			// Raw URI is set, it must be parsed and already set URI parts must take precedence
			$this->_parts = array_merge(URI::parse($this->_raw_uri), $this->_parts);
			// Reset raw URI
			$this->_raw_uri = NULL;
		}
	}

	/**
	 * @var  array  URI part names, used by `URI::parse`.
	 */
	protected static $_part_names = array('uri', 'scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment');

	/**
	 * URI class factory.
	 * 
	 * @param mixed $uri
	 * @param array $parameters
	 * @return URI
	 */
	public static function factory($uri = NULL, $parameters = array())
	{
		return new URI($uri, $parameters);
	}

	/**
	 * Parses string into URI parts array.
	 *
	 * @param   string  $uri
	 * @return  array
	 */
	public static function parse($uri)
	{
		// XXX: `parse_url()` might be a better choise, need benchmarks...

		// Parse URI string
		preg_match('/^(?:(\w+):)?(?:\/\/(?:(?:([^:@\/]*):?([^:@\/]*))?@)?([^:\/?#]*)(?::(\d*))?)?([^?#]*)(?:\?([^#]*))?(?:#(.*))?/', $uri, $parts);

		// Combine matches with names to get associative array
		$parts = array_combine(URI::$_part_names, array_pad($parts, count(URI::$_part_names), NULL));

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

	/**
	 * Joins URI parts into a string
	 *
	 * @param   array   $parts
	 * @return  string
	 */
	public static function render(array $parts)
	{
		// XXX: `http_build_url()` belongs to PECL library, not necessarily available...

		return ($parts['scheme'] ? $parts['scheme'].'://' : '')
			.(($parts['user'] OR $parts['pass']) ? $parts['user'].':'.$parts['pass'].'@' : '')
			.($parts['host'] ? $parts['host'] : '')
			.($parts['port'] ? ':'.$parts['port'] : '')
			.(($parts['path'] AND (strpos($parts['path'], '/') !== 0 AND ($parts['host'] OR $parts['port']))) ? '/' : '').$parts['path']
			.($parts['query'] ? '?'.http_build_query($parts['query'], NULL, '&') : '')
			.($parts['fragment'] ? '#'.$parts['fragment'] : '');
	}
}