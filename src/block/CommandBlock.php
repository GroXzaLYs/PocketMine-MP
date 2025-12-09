<?php

declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\block\tile\CommandBlock as CommandBlockTile;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\item\ItemBlock;
use pocketmine\world\BlockTransaction;
use pocketmine\world\sound\ClickSound;
use pocketmine\network\mcpe\protocol\CommandBlockUpdatePacket;
use pocketmine\network\mcpe\protocol\types\CommandBlockMode;

class CommandBlock extends Opaque{

	public const IMPULSE = 0;
	public const REPEATING = 1;
	public const CHAIN = 2;

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