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

namespace pocketmine\world\biome;

use pocketmine\world\generator\object\TreeType;
use pocketmine\world\generator\populator\TallGrass;
use pocketmine\world\generator\populator\Tree;

class RoofedForestBiome extends GrassyBiome{

	public function __construct(){
		parent::__construct();

		$tallGrass = new TallGrass();
		$tallGrass->setBaseAmount(3);
		$this->addPopulator($tallGrass);

		$trees = new Tree(TreeType::DARK_OAK);
		$trees->setBaseAmount(15);
		$this->addPopulator($trees);

		$oak = new Tree(TreeType::OAK);
		$oak->setBaseAmount(6); //idk
		$this->addPopulator($oak);

		$birch = new Tree(TreeType::BIRCH);
		$birch->setBaseAmount(6); //idk
		$this->addPopulator($birch);

		$this->setElevation(62, 78);

		$this->temperature = 0.7;
		$this->rainfall = 0.8;
	}

	public function getName() : string{
		return "";
	}
}
