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

namespace pocketmine\block\tile;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Server;
use function date;

class CommandBlock extends Tile{

	private string $command = "";
	private string $name = "@";
	private string $filteredName = "";

	private string $lastOutput = "";
	private bool $shouldTrackOutput = false;

	private string $mode = "IMPULSE"; // IMPULSE | REPEAT | CHAIN
	private bool $conditional = false;
	private bool $needsRedstone = true;

	private int $tickDelay = 0;
	private bool $executeOnFirstTick = false;

	private int $cooldown = 0;

	public function readSaveData(CompoundTag $nbt) : void{
		$this->command = $nbt->getString("Command", "");
		$this->name = $nbt->getString("CustomName", "@");
		$this->filteredName = $nbt->getString("FilteredName", "");

		$this->mode = $nbt->getString("Mode", "IMPULSE");
		$this->conditional = $nbt->getByte("Conditional", 0) === 1;
		$this->needsRedstone = $nbt->getByte("NeedsRedstone", 1) === 1;
		$this->shouldTrackOutput = $nbt->getByte("TrackOutput", 0) === 1;
		$this->lastOutput = $nbt->getString("LastOutput", "");
		$this->tickDelay = $nbt->getInt("TickDelay", 0);
		$this->executeOnFirstTick = $nbt->getByte("ExecuteOnFirstTick", 0) === 1;
	}

	public function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setString("Command", $this->command);
		$nbt->setString("CustomName", $this->name);
		$nbt->setString("FilteredName", $this->filteredName);

		$nbt->setString("Mode", $this->mode);
		$nbt->setByte("Conditional", $this->conditional ? 1 : 0);
		$nbt->setByte("NeedsRedstone", $this->needsRedstone ? 1 : 0);
		$nbt->setByte("TrackOutput", $this->shouldTrackOutput ? 1 : 0);
		$nbt->setString("LastOutput", $this->lastOutput);
		$nbt->setInt("TickDelay", $this->tickDelay);
		$nbt->setByte("ExecuteOnFirstTick", $this->executeOnFirstTick ? 1 : 0);
	}

	public function setCommand(string $cmd) : void{
		$this->command = $cmd;
	}
	public function getCommand() : string{
		return $this->command;
	}

	public function setName(string $name) : void{
		$this->name = $name;
	}
	public function getName() : string{
		return $this->name;
	}

	public function setFilteredName(string $name) : void{
		$this->filteredName = $name;
	}
	public function getFilteredName() : string{
		return $this->filteredName;
	}

	public function setLastOutput(string $output) : void{
		$this->lastOutput = $output;
	}
	public function getLastOutput() : string{
		return $this->lastOutput;
	}

	public function setTrackOutput(bool $v) : void{
		$this->shouldTrackOutput = $v;
	}
	public function shouldTrackOutput() : bool{
		return $this->shouldTrackOutput;
	}

	public function setMode(string $mode) : void{
		$this->mode = $mode;
	}
	public function getMode() : string{
		return $this->mode;
	}

	public function setConditional(bool $v) : void{
		$this->conditional = $v;
	}
	public function isConditional() : bool{
		return $this->conditional;
	}

	public function setNeedsRedstone(bool $v) : void{
		$this->needsRedstone = $v;
	}
	public function needsRedstone() : bool{
		return $this->needsRedstone;
	}

	public function setTickDelay(int $delay) : void{
		$this->tickDelay = $delay;
	}
	public function getTickDelay() : int{
		return $this->tickDelay;
	}

	public function setExecuteOnFirstTick(bool $v) : void{
		$this->executeOnFirstTick = $v;
	}
	public function executeOnFirstTick() : bool{
		return $this->executeOnFirstTick;
	}

	public function onUpdate() : bool{
		if($this->cooldown > 0){
			$this->cooldown--;
			return true;
		}

		if($this->mode === "REPEAT"){
			if(!$this->needsRedstone){
				$this->runCommand();
			}
		}

		return true;
	}

	public function runCommand() : void{
		if($this->conditional){
			if(!$this->checkConditional()){
				return;
			}
		}

		if($this->tickDelay > 0){
			$this->cooldown = $this->tickDelay;
		}

		$sender = new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage());
		$result = Server::getInstance()->dispatchCommand($sender, $this->command);

		if($this->shouldTrackOutput){
			$this->lastOutput = date("H:i:s") . " - Executed";
		}

		if($this->mode === "CHAIN"){
			$this->runChainNext();
		}
	}

	private function runChainNext() : void{
		$block = $this->getPosition()->getWorld()->getBlock($this->getPosition());
		$face = $block->getFacing();
		$nextPos = $this->getPosition()->getSide($face);

		$nextTile = $this->getWorld()->getTile($nextPos);
		if($nextTile instanceof CommandBlock){
			if($nextTile->getMode() === "CHAIN"){
				$nextTile->runCommand();
			}
		}
	}
}
