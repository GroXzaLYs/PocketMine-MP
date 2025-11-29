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

namespace pocketmine\world\generator\object;

use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;

final class DarkOakTree extends Tree{

	private const MIN_HEIGHT = 6;

	public function __construct(){
		parent::__construct(
			VanillaBlocks::DARK_OAK_LOG(),
			VanillaBlocks::DARK_OAK_LEAVES(),
			0
		);
	}

	protected function generateTrunkHeight(Random $random) : int{
		return self::MIN_HEIGHT + $random->nextBoundedInt(4); // 6–9
	}

	protected function placeTrunk(int $x, int $y, int $z, Random $random, int $height, BlockTransaction $transaction) : void{
		// 2x2 dirt base
		$base = [
			[$x, $y - 1, $z],
			[$x + 1, $y - 1, $z],
			[$x, $y - 1, $z + 1],
			[$x + 1, $y - 1, $z + 1]
		];

		foreach($base as $v){
			[$bx, $by, $bz] = $v;
			$transaction->addBlockAt($bx, $by, $bz, VanillaBlocks::DIRT());
		}

		// 2x2 trunk
		for($yy = 0; $yy < $height; $yy++){
			$layer = [
				[$x, $y + $yy, $z],
				[$x + 1, $y + $yy, $z],
				[$x, $y + $yy, $z + 1],
				[$x + 1, $y + $yy, $z + 1]
			];

			foreach($layer as $v){
				[$tx, $ty, $tz] = $v;
				$transaction->addBlockAt($tx, $ty, $tz, $this->trunkBlock);
			}
		}
	}

	protected function placeCanopy(int $x, int $y, int $z, Random $random, BlockTransaction $transaction) : void{
		$top = $y + 4 + $random->nextBoundedInt(2);

		for($yy = $top - 2; $yy <= $top; $yy++){
			$radius = ($top - $yy) + 2;

			for($xx = $x - $radius; $xx <= $x + $radius + 1; $xx++){
				for($zz = $z - $radius; $zz <= $z + $radius + 1; $zz++){

					if((($xx - $x) ** 2) + (($zz - $z) ** 2) <= ($radius ** 2) + 1){
						$block = $transaction->fetchBlockAt($xx, $yy, $zz);

						if($block->canBeReplaced()){
							$transaction->addBlockAt($xx, $yy, $zz, $this->leafBlock);
						}
					}
				}
			}
		}
	}
}
