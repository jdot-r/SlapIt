<?php

namespace slapit\execution\operation;

use pocketmine\command\Command;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\String;
use slapit\entity\SlappableHuman;
use slapit\execution\selector\Selector;

class CmdOperation extends Operation{
	const VERSION_INITIAL = 1; // initial version

	const CURRENT_VERSION = self::VERSION_INITIAL;

	/** @var SlappableHuman */
	private $human;
	/** @var int */
	private $version;
	private $runCmds;
	public function __construct(SlappableHuman $human, Compound $nbt){
		$this->human = $human;
		$this->version = $nbt->_Version->getValue();
		if($this->version !== self::CURRENT_VERSION){
			$this->adaptVersion();
		}
		/** @var Enum $runs */
		$runs = $nbt->RunCmds;
		/** @var \pocketmine\nbt\tag\String $run */
		foreach($runs as $run){
			$this->runCmds[] = $run->getValue();
		}
	}
	private function adaptVersion(){
		// add things here in newer versions
		$this->version = self::CURRENT_VERSION;
	}
	public function onRun(){
		foreach($this->runCmds as $run){
			$this->dispatchCmd($run);
		}
	}
	private function dispatchCmd($cmd){
		$map = $this->getServer()->getCommandMap();
		foreach(Selector::processCmd($cmd) as $cmd){
			$args = explode(" ", $cmd);
			$cmdName = array_shift($args);
			if(($command = $map->getCommand($cmdName)) instanceof Command){
				$command->execute($this->human, $cmdName, $args); // sender is not console
			}
			else{
				// TODO
			}
		}
	}
	public function writeNBT(Compound $nbt){
		$nbt->RunCmds = new Enum("RunCmds", array_map(function($cmd){
			return new String($cmd);
		}, $this->runCmds));
	}
	public function getSaveVersion(){
		return self::CURRENT_VERSION;
	}
	/**
	 * @return SlappableHuman
	 */
	public function getHuman(){
		return $this->human;
	}
	public function getContext(){
		return $this->getServer()->getPluginManager()->getPlugin("SlapIt");
	}
}
