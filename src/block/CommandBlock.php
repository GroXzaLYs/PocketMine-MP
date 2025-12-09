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

namespace pocketmine\block;

use pocketmine\block\tile\CommandBlock as CommandBlockTile;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\CommandBlockUpdatePacket;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\world\sound\ClickSound;

class CommandBlock extends Opaque{

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		$tile = $player->getWorld()->getTile($blockPos);

		if(!$tile instanceof CommandBlockTile){
			return false;
		}

		$player->broadcastSound(new ClickSound(), [$player]);

		$packet = new CommandBlockUpdatePacket();
		$packet->isBlock = true;
		$packet->blockPosition = $blockPos;
		$packet->commandBlockMode = $tile->getModeValue();
		$packet->isRedstoneMode = $tile->needsRedstone();
		$packet->isConditional = $tile->isConditional();

		$packet->command = $tile->getCommand();
		$packet->lastOutput = $tile->getLastOutput();
		$packet->name = $tile->getNameTag();
		$packet->filteredName = "";
		$packet->shouldTrackOutput = $tile->isTrackingOutput();
		$packet->tickDelay = $tile->getTickDelay();
		$packet->executeOnFirstTick = $tile->executeOnFirstTick();

		$player->getNetworkSession()->sendDataPacket($packet);
		return true;
	}

	public function onBlockPlaced(BlockTransaction $tx, Item $item, Block $blockReplace, Vector3 $pos) : void{
		$world = $tx->getWorld();
		$world->setTile($pos, new CommandBlockTile($world, $pos));
	}

	public function getDrops(Item $item) : array{
		return [new ItemBlock($this, 1)];
	}

	public function hasEntityCollision() : bool{
		return false;
	}
}
