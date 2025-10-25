<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\entity;

use pocketmine\entity\ai\TargetFinder;
use pocketmine\entity\animation\ArmSwingAnimation;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;
use function mt_rand;
use function rad2deg;
use function sqrt;

class Zombie extends Living{

	public static function getNetworkTypeId() : string{ return EntityIds::ZOMBIE; }

	protected ?Player $target = null;
	protected int $wanderTick = 0;
	protected int $attackCooldown = 0;

	protected function getInitialSizeInfo() : EntitySizeInfo{
		return new EntitySizeInfo(1.9, 0.6);
	}

	public function getName() : string{
		return "Zombie";
	}

	public function getDrops() : array{
		$drops = [
			VanillaItems::ROTTEN_FLESH()->setCount(mt_rand(0, 2))
		];

		if(mt_rand(0, 199) < 5){
			switch(mt_rand(0, 2)){
				case 0:
					$drops[] = VanillaItems::IRON_INGOT();
					break;
				case 1:
					$drops[] = VanillaItems::CARROT();
					break;
				case 2:
					$drops[] = VanillaItems::POTATO();
					break;
			}
		}
		return $drops;
	}

	public function getXpDropAmount() : int{
		//TODO: check for equipment and whether it's a baby
		return 5;
	}

	public function getPickedItem() : ?Item{
		return VanillaItems::ZOMBIE_SPAWN_EGG();
	}

	public function onUpdate(int $currentTick) : bool{
		if(!$this->isAlive()){
			return parent::onUpdate($currentTick);
		}

		if($this->attackCooldown > 0){
			$this->attackCooldown--;
		}

		if($this->target === null || !$this->target->isAlive() || $this->target->isClosed()){
			$this->target = TargetFinder::findNearestPlayer($this, 16);
		}

		if($this->target instanceof Player){
			$distance = $this->location->distance($this->target->getLocation());
			if($distance <= 1.6){
				$this->lookAt($this->target->getPosition());
				$this->tryAttack($this->target);
			}else{
				$this->moveToward($this->target->getPosition(), 0.20);
			}
		}else{
			if($this->wanderTick++ > 60){
				$this->wanderTick = 0;
				$rand = new Vector3(
					$this->location->x + mt_rand(-6, 6),
					$this->location->y,
					$this->location->z + mt_rand(-6, 6)
				);
				$this->moveToward($rand, 0.1);
			}
		}

		return parent::onUpdate($currentTick);
	}

	public function lookAt(Vector3 $target) : void{
		$dx = $target->x - $this->location->x;
		$dz = $target->z - $this->location->z;
		$this->setRotation(rad2deg(atan2(-$dx, $dz)), 0);
	}

	protected function moveToward(Vector3 $pos, float $speed) : void{
		$dx = $pos->x - $this->location->x;
		$dz = $pos->z - $this->location->z;
		$length = sqrt($dx * $dx + $dz * $dz);
		if($length == 0){
			return;
		}
		$this->motion->x = $dx / $length * $speed;
		$this->motion->z = $dz / $length * $speed;
		$this->setRotation(rad2deg(atan2(-$dx, $dz)), 0);
		$this->updateMovement();
	}

	protected function tryAttack(Entity $entity) : void{
		if($this->attackCooldown > 0){
			return;
		}
		$this->attackCooldown = 20;
		$damage = 3;

		$ev = new EntityDamageByEntityEvent($this, $entity, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $damage);
		$entity->attack($ev);

		if(!$ev->isCancelled()){
			$this->broadcastAnimation(new ArmSwingAnimation($this));
		}
	}
}
