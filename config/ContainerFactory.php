<?php


namespace Genesis\Config;


use Nette\Neon\Decoder;


/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class ContainerFactory
{

	private $configs;

	private $containersToMerge;

	private $workingDirectory;


	public function addConfig($file)
	{
		$this->configs[] = $file;
	}


	public function getConfigs()
	{
		return $this->configs;
	}


	public function addContainerToMerge(Container $container)
	{
		$this->containersToMerge[] = $container;
	}


	public function getContainersToMerge()
	{
		return $this->containersToMerge;
	}


	public function getWorkingDirectory()
	{
		return $this->workingDirectory;
	}


	public function setWorkingDirectory($workingDirectory)
	{
		$this->workingDirectory = $workingDirectory;
	}


	public function create()
	{
		if (!class_exists('Nette\Neon\Decoder', TRUE)) {
			throw new \RuntimeException("Neon is not loaded.");
		}
		if (empty($this->configs)) {
			throw new \RuntimeException("No config added.");
		}
		$config = [
			'workingDirectory' => $this->workingDirectory,
		];
		if($this->containersToMerge){
			foreach ($this->containersToMerge as $containerToMerge) {
				foreach ($containerToMerge as $k => $v) {
					$config[$k] = $v;
				}
			}
		}
		$neonDecoder = new Decoder;
		foreach ($this->configs as $file) {
			if (!is_file($file)) {
				throw new \RuntimeException("Config file '$file' not found.");
			}
			if (!is_readable($file)) {
				throw new \RuntimeException("Config file '$file' not readable.");
			}
			$config = array_merge($config, $neonDecoder->decode(file_get_contents($file)));
		}
		$this->parseValues($config, $config);
		return new Container($config);
	}


	private function parseValues(& $config, array $values)
	{
		foreach ($values as $key => $value) {
			$newKey = $this->parseValue($config, $key);
			if (is_array($value)) {
				$value = $this->parseValues($config, $value);
			} elseif(!is_object($value)) {
				$value = $this->parseValue($config, $value);
			}
			unset($values[$key], $config[$key]);
			$values[$newKey] = $value;
			$config[$newKey] = $value;
		}
		return $values;
	}


	private function parseValue($config, $value)
	{
		if (preg_match_all('#%([^%]+)%#', $value, $matches)) {
			foreach ($matches[1] as $match) {
				if (!isset($config[$match])) {
					throw new \RuntimeException("Cannot find variable '$match'.");
				}
				$value = str_replace("%$match%", $config[$match], $value); // TODO: nested variables
			}
		}
		return $value;
	}

}