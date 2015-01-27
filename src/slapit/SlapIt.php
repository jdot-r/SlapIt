<?php

namespace slapit;

use pocketmine\entity\Entity;
use pocketmine\plugin\PluginBase;
use slapit\entity\SlappableHuman;

class SlapIt extends PluginBase{
	public function onEnable(){
		Entity::registerEntity(SlappableHuman::class);
	}
}
