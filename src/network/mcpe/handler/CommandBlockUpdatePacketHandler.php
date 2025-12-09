<?php

declare(strict_types=1);

namespace pocketmine\network\mcpe\handler;

use pocketmine\network\mcpe\protocol\CommandBlockUpdatePacket;
use pocketmine\network\mcpe\handler\PacketHandler;
use pocketmine\player\Player;
use pocketmine\block\tile\CommandBlock as CommandBlockTile;
use pocketmine\network\mcpe\protocol\types\CommandBlockMode;
use pocketmine\network\mcpe\protocol\types\CommandBlockUpdateType;

class CommandBlockUpdatePacketHandler extends PacketHandler{

    private Player $player;

    public function __construct(Player $player){
        $this->player = $player;
    }

    public function handleCommandBlockUpdate(CommandBlockUpdatePacket $packet) : bool{
        if($packet->type !== CommandBlockUpdateType::UPDATE){
            return true;
        }

        $pos = $packet->blockPosition;
        $world = $this->player->getWorld();
        $tile = $world->getTile($pos);

        if(!$tile instanceof CommandBlockTile){
            return true;
        }

        $tile->setCommand($packet->command);

        $mode = match($packet->mode){
            CommandBlockMode::REPEATING => "REPEAT",
            CommandBlockMode::CHAIN => "CHAIN",
            default => "IMPULSE"
        };
        $tile->setMode($mode);
        $tile->setConditional($packet->conditional);
        $tile->setNeedsRedstone($packet->redstoneMode);

        $tile->setDirty();

        return true;
    }
}