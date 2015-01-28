<?php

namespace slapit\execution;

use pocketmine\nbt\tag;
use pocketmine\scheduler\PluginTask;
use slapit\entity\SlappableHuman;
use slapit\execution\operation\Operation;
use slapit\SlapIt;

class RepeatingOperation extends PluginTask{
	private $ticks;
	/** @var SlappableHuman */
	private $human;
	/** @var Operation[] */
	private $operations;
	public function __construct(SlapIt $owner, SlappableHuman $human, $arg){
		parent::__construct($owner);
		$this->human = $human;
		if($arg instanceof tag\Compound){
			$nbt = $arg;
			$this->ticks = $nbt->RepeatingTicks;
			/** @var tag\Enum $operations */
			$operations = $nbt->Operations;
			foreach($operations->getValue() as $sub){
				$op = Operation::constructFromNBT($owner, $human, $sub);
				if($op === null){
					continue;
				}
				$this->operations[] = $op;
			}
		}
		elseif(is_array($arg)){
			foreach($arg as $op){
				if(!($op instanceof Operation)){
					throw new \InvalidArgumentException;
				}
				$this->operations[] = $op;
			}
		}
		$this->owner->getServer()->getScheduler()->scheduleRepeatingTask($this, $this->ticks);
	}
	public function onRun($tick){
		foreach($this->operations as $oper){
			$oper->onRun();
		}
	}
}
