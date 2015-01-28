<?php

namespace slapit\execution\operation;

use pocketmine\nbt\tag;
use pocketmine\plugin\Plugin;
use slapit\entity\SlappableHuman;
use slapit\SlapIt;

abstract class Operation{
	public static function registerOperationType(Plugin $ctx, $type, $overwrite = false){
		if(isset(self::$registeredTypes[$type]) and !$overwrite){
			throw new \RuntimeException(sprintf("Type %s is already registered by context %s.", $type, self::$registeredTypes[$type][1]));
		}
		try{
			$ref = new \ReflectionClass($type);
			if(!$ref->isSubclassOf(self::class)){
				throw new \ReflectionException("Class $type must extend " . get_class());
			}
			$cstr = $ref->getConstructor();
			if(!($cstr instanceof \ReflectionMethod)){
				throw new \ReflectionException("Class $type doesn't have a constructor");
			}
			if($cstr->getNumberOfRequiredParameters() > 2){
				throw new \ReflectionException("Class $type requires too many parameters");
			}
			/** @var \ReflectionParameter $arg0 */
			/** @var \ReflectionParameter $arg1 */
			list($arg0, $arg1) = $cstr->getParameters();
			if($arg0->getClass() !== SlappableHuman::class and $arg0->getClass() !== null or $arg1->getClass() !== tag\Compound::class and $arg1->getClass() !== null){
				throw new \ReflectionException("Class $type accepts invalid parameter types");
			}
			self::$registeredTypes[$type] = [$ref, $ctx->getName()];
		}catch(\ReflectionException $ex){
			throw new \RuntimeException($ex->getMessage(), 0, $ex);
		}
	}
//	public abstract function __construct(SlappableHuman $human, tag\Compound $nbt);
	public abstract function onRun();
	public abstract function writeNBT(tag\Compound $compound);
	/**
	 * @return string
	 */
	public abstract function getSaveVersion();
	/**
	 * @return Plugin
	 */
	public abstract function getContext();
	/**
	 * @return SlappableHuman
	 */
	public abstract function getHuman();
	/** @var string[][] */
	public static $registeredTypes = [];
	/**
	 * @param SlapIt $plugin
	 * @param SlappableHuman $human
	 * @param tag\Compound $nbt
	 * @return Operation
	 */
	public static function constructFromNBT(SlapIt $plugin, SlappableHuman $human, tag\Compound $nbt){
		/** @var tag\String $type */
		$type = $nbt->_Type;
		$type = $type->getValue();
		/** @var tag\String $typeCtx */
		$typeCtx = $nbt->_TypeContext;
		$typeCtx = $typeCtx->getValue();
		/** @var tag\Short $version */
		$version = $nbt->_Version;
		$version = $version->getValue();
		if(!isset(self::$registeredTypes[$type])){
			$plugin->getLogger()->warning("Failed to load an operation for a SlappableHuman (type $type, version $version): Unknown operation type. ");
			if($typeCtx === "SlapIt"){
				$plugin->getLogger()->warning("Are you using a map that contains a SlappableHuman spawned by a newer version of SlapIt?");
			}
			$plugin->getLogger()->warning("The type was registered by the plugin $typeCtx. Please install that plugin. If you already did, please contact its author.");
			return null;
		}
		/** @var \ReflectionClass $class */
		list($class) = self::$registeredTypes[$type];
		$class->getConstructor()->invoke(null, $human, $nbt);
	}
	public final function saveNBT(){
		$nbt = new tag\Compound;
		$nbt["_Type"] = new tag\String("_Type", get_class($this));
		$nbt["_TypeContext"] = new tag\String("_TypeContext", $this->getContext()->getName());
		$nbt["_Version"] = new tag\Short("_Version", $this->getSaveVersion());
		$this->writeNBT($nbt);
		return $nbt;
	}
	public function getServer(){
		return $this->getHuman()->getServer();
	}
}
