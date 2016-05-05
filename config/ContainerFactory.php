<?php


namespace Genesis\Config;


use Nette\Neon\Decoder;


/**
 * @author Adam Bisek <adam.bisek@gmail.com>
 */
class ContainerFactory
{

	private $configs;

	/** @var Container[] */
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
			'parameters' => [
				'workingDirectory' => $this->workingDirectory,
			]
		];
		if($this->containersToMerge){
			foreach ($this->containersToMerge as $containerToMerge) {
				foreach ($containerToMerge->getParameters() as $k => $v) {
					$config['parameters'][$k] = $v;
				}
				foreach ($containerToMerge->getServices() as $k => $v) {
					$config['services'][$k] = $v;
				}
			}
		}
		$configs = $this->resolveFiles($this->configs);
		$config = $this->readConfigs($configs, $config);
		$config = $this->parseValues($config);

		// BC break check
		$mainSections = ['includes', 'class', 'parameters', 'services'];
		foreach($config as $key => $val){
			if(!in_array($key, $mainSections)) {
				throw new \RuntimeException("Since version 2.0 are supported main only these sections: " . implode(", ", $mainSections) . ". Section '$key' found. Move your variables into parameters section.");
			}
		}

		$container = new Container();
		$container->setClass($config['class']);
		if(isset($config['parameters'])){
			$container->setParameters($config['parameters']);
		}
		if(isset($config['services'])){
			foreach($config['services'] as $name => $config){
				if(!is_array($config)){
					$container->addService($name, $config); // is directly service object from merged container
					continue;
				}
				$class = $config['class'];
				$arguments = [];
				if($config['class'] instanceof \Nette\Neon\Entity){
					$class = $config['class']->value;
					$arguments = $config['class']->attributes;
				}
				$reflectionClass = new \ReflectionClass($class);
				$service = $reflectionClass->newInstanceArgs($arguments);
				if(isset($config['setup'])){
					foreach($config['setup'] as $neonEntity){
						call_user_func_array(array($service, $neonEntity->value), $neonEntity->attributes);
					}
				}
				$container->addService($name, $service);
			}
		}

		return $container;
	}


	private function resolveFiles(array $files)
	{
		$return = [];
		foreach ($files as $file) {
			$array = $this->readFile($file);
			if($array !== NULL){
				if(isset($array['includes'])){
					foreach($array['includes'] as $include){
						$return[] = dirname($file) . DIRECTORY_SEPARATOR . $include;
					}
				}
				$return[] = $file;
			}
		}
		return $return;
	}


	private function readConfigs($files, $config)
	{
		foreach ($files as $file) {
			$array = $this->readFile($file);
			if($array !== NULL) {
				$config = array_replace_recursive($config, $array);
			}
		}
		return $config;
	}


	private function readFile($file)
	{
		$neonDecoder = new Decoder;
		if (!is_file($file)) {
			throw new \RuntimeException("Config file '$file' not found.");
		}
		if (!is_readable($file)) {
			throw new \RuntimeException("Config file '$file' not readable.");
		}
		return $neonDecoder->decode(file_get_contents($file));
	}


	private function parseValues($config, & $allConfig = [], $keysPath = [])
	{
		$config = $this->resolveUnmergables($config);
		foreach ($config as $key => $value) {
			if($value instanceof \Nette\Neon\Entity){
				$value->value = $this->parseValue($value->value, $allConfig);
				foreach($value->attributes as $k => $v){
					if(is_array($v)){
						$value->attributes[$k] = $this->parseValues($v, $allConfig, array_merge($keysPath, [$key]));
					}else{
						$value->attributes[$k] = $this->parseValue($v, $allConfig);
					}
				}
			}elseif (is_array($value)) {
				$value = $this->parseValues($value, $allConfig, array_merge($keysPath, [$key]));
			} elseif(!is_object($value)) {
				$value = $this->parseValue($value, $allConfig);
			}

			// get new key name, and replace it
			$newKey = $this->parseValue($key, $allConfig);
			unset($config[$key]);
			$config[$newKey] = $value;

			// write to global config
			$v =& $allConfig;
			foreach ($keysPath as $kp) {
				$v =& $v[$kp];
			}
			$v[$newKey] = $value;
		}
		return $config;
	}


	private function parseValue($value, $config)
	{
		if (preg_match_all('#%([^%]+)%#', $value, $matches)) {
			foreach ($matches[1] as $match) {
				if (!array_key_exists($match, $config['parameters'])) {
					throw new \RuntimeException("Cannot find variable '$match'.");
				}
				$value = str_replace("%$match%", $config['parameters'][$match], $value); // TODO: nested variables
			}
		}
		return $value;
	}


	private function resolveUnmergables($config)
	{
		foreach ($config as $key => $value) {
			if(preg_match('#!$#', $key)){
				$newKey = substr($key, 0, strlen($key) - 1);
				$config[$newKey] = $value;
				unset($config[$key]);
			}
		}
		return $config;
	}

}