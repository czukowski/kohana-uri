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
	 * @var string  flag to determine, whether the path is absolute
	 */
	protected $_is_absolute = NULL;
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
	 * @param  mixed  $uri
	 */
	public function __construct($uri = NULL)
	{
		if (is_array($uri))
		{
			// $uri parameter is array, assume it's URI parts
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
	 * @return  string
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
	 * @return  boolean
	 */
	public function is_absolute()
	{
		return $this->_is_absolute;
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
	 * Changes relative URI to absolute
	 * 
	 * @param   mixed  $base
	 * @return  URI
	 */
	public function to_absolute($base = NULL)
	{
		if ( ! $this->is_absolute())
		{
			$this->set('path', UTF8::rtrim($this->_get_base($base), '/').'/'.UTF8::ltrim($this->get('path'), '/'));
			$this->_is_absolute = TRUE;
		}
		return $this;
	}

	/**
	 * Changes absolute URI to relative
	 * 
	 * @param   mixed  $base
	 * @return  URI
	 */
	public function to_relative($base = NULL)
	{
		if ($this->is_absolute())
		{
			$base = $this->_get_base($base);
			$path = $this->get('path');
			if (UTF8::strpos($path, $base) === 0)
			{
				$this->set('path', UTF8::ltrim(UTF8::substr($path, UTF8::strlen($base) + 1)), '/');
				$this->_is_absolute = FALSE;
			}
			else
			{
				throw new Kohana_Exception('Absolute URI path does not start with the specified string: :string', array(':string' => $base));
			}
		}
		return $this;
	}

	/**
	 * Returns passed parameter, or Kohana base URL with index, if parameter was NULL
	 * 
	 * @param   mixed   $base
	 * @return  string
	 */
	protected function _get_base($base = NULL)
	{
		if ($base !== NULL)
		{
			return $base;
		}
		return Kohana::$base_url.Kohana::$index_file;
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
	 * @param   mixed  $uri
	 * @return  URI
	 */
	public static function factory($uri = NULL)
	{
		return new URI($uri);
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
	 * Parses string into URI parts array.
	 *
	 * @param   string  $uri
	 * @return  array
	 */
	public static function parse_url($uri)
	{
		$parts = parse_url($uri);

		// Combine matches with names to get associative array
		$parts = array_merge(array_combine(URI::$_part_names, array_pad(array(), count(URI::$_part_names), NULL)), $parts);

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

		// Parse query string
		if ($parts['port'] !== NULL)
		{
			$parts['port'] = (string) $parts['port'];
		}

		$parts['uri'] = $uri;

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