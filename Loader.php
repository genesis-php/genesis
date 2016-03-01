<?php


namespace Genesis;


/**
 * @author Adam Bisek (adam.bisek@gmail.com)
 */
class Loader
{

	/** @var array */
	private $map;


	public function __construct(array $map = array('Genesis' => __DIR__))
	{
		$this->map = $map;
	}


	/**
	 * Register autoloader.
	 */
	public function register()
	{
		spl_autoload_register(array($this, 'tryLoad'));
		return $this;
	}


	/**
	 * @param  string
	 */
	private function tryLoad($type)
	{
		$type = ltrim($type, '\\');
		foreach ($this->map as $ns => $dir) {
			if ($this->stringStartsWith($type, $ns)) {
				$file = str_replace('\\', DIRECTORY_SEPARATOR, $type) . '.php';
				$file = ltrim(substr($file, strlen($ns) + 1));
				$path = substr($file, 0, strrpos($file, DIRECTORY_SEPARATOR));
				$file = str_replace($path, strtolower($path), $file);
				$file = $dir . DIRECTORY_SEPARATOR . $file;
				if (!is_file($file)) {
					throw new \RuntimeException("File '$file' does not exists.");
				}
				return require_once $file;
			}
		}
	}


	private function stringStartsWith($haystack, $needle)
	{
		return strncmp($haystack, $needle, strlen($needle)) === 0;
	}

}