<?php

namespace slapit\entity;

use pocketmine\command\CommandSender;
use pocketmine\entity\Human;
use pocketmine\level\format\FullChunk;
use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Float;
use pocketmine\nbt\tag\Short;
use pocketmine\permission\PermissibleBase;
use pocketmine\permission\PermissionAttachment;
use pocketmine\plugin\Plugin;
use slapit\cmd\CmdArgMap;

class SlappableHuman extends Human implements CommandSender{
	/** @var null|CommandSender */
	private $sender = null;
	/** @var PermissibleBase */
	private $perm;
	public function __construct(FullChunk $chunk, Compound $nbt){
		parent::__construct($chunk, $nbt);
		$this->perm = new PermissibleBase($this);
	}
	public static function create(Position $pos, CmdArgMap $args){
		$pos = new Location($pos->x, $pos->y, $pos->z, $args->getOptArg("yaw", 0.0), $args->getOptArg("pitch", 0.0), $pos->getLevel());
		$nbt = new Compound;
		$nbt->NameTag = $args->getReqArg(0);
		$nbt->Pos = new Enum("Pos", [
			new Double(0, $pos->x),
			new Double(1, $pos->y),
			new Double(2, $pos->z),
		]);
		$nbt->Motion = new Enum("Motion", [
			new Double(0, $args->getOptArg("speedx", 0.0)),
			new Double(1, $args->getOptArg("speedy", 0.0)),
			new Double(2, $args->getOptArg("speedz", 0.0)),
		]);
		$nbt->Rotation = new Enum("Rotation", [
			new Float(0, $pos->yaw),
			new Float(1, $pos->pitch)
		]);
		$nbt->FallDistance = new Float("FallDistance", 0.0);
		$nbt->Fire = new Short("Fire", (int) ($args->getOptArg("burntime", 0) * 20));
		$nbt->Air = new Short("Air", 0);
		$nbt->OnGround = new Byte("OnGround", 1);
		$nbt->Invulnerable = new Byte("Invulnerable", 1);
		$nbt->Health = new Short("Health", (int) ($args->getOptArg("health", 10) * 2));
		$nbt->Inventory = new Enum("Inventory", [new Compound(false, [
			new Short("id", 0),
			new Short("Damage", 0),
			new Byte("Count", 0),
			new Byte("Slot", 9),
			new Byte("TrueSlot", 9)
		])]);
		$nbt->SlapItData = new Compound("SlapItData", [
			new Enum("OnSlapOperation"),
			new Enum("RepeatingOperations"),
			new Byte("Crouched", 0),
			new Byte("InAction", 0)
		]);
		return new self($pos->getLevel()->getChunk($pos->getFloorX() >> 4, $pos->getFloorZ() >> 4, true), $nbt);
	}
	public function getServer(){
		return $this->getLevel()->getServer();
	}
	public function sendMessage($message){
		if($this->sender instanceof CommandSender){
			$this->sender->sendMessage($message);
		}
	}
	public function isPermissionSet($name){
		return $this->perm->isPermissionSet($name);
	}
	public function hasPermission($name){
		return $this->perm->hasPermission($name);
	}
	public function addAttachment(Plugin $plugin, $name = null, $value = null){
		return $this->perm->addAttachment($plugin, $name, $value);
	}
	public function removeAttachment(PermissionAttachment $attachment){
		$this->perm->removeAttachment($attachment);
	}
	public function recalculatePermissions(){
		$this->perm->recalculatePermissions();
	}
	public function getEffectivePermissions(){
		return $this->perm->getEffectivePermissions();
	}
	public function isPlayer(){
		return false;
	}
	public function setOp($op){}
	public function isOp(){
		return true;
	}
	public function getName(){
		return "SlappableHuman";
	}
}
