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
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use function abs;
use function floor;

final class PaleOakTree extends Tree{
	private const MIN_HEIGHT = 6;

	public function __construct(){
		parent::__construct(
			VanillaBlocks::PALE_OAK_LOG(),
			VanillaBlocks::PALE_OAK_LEAVES(),
			0
		);
	}

	protected function generateTrunkHeight(Random $random) : int{
		return self::MIN_HEIGHT + $random->nextRange(0, 2);
	}

	protected function placeTrunk(int $x, int $y, int $z, Random $random, int $height, BlockTransaction $transaction) : void{
		for($yy = 0; $yy < $height; $yy++){
			$transaction->addBlockAt($x,     $y + $yy, $z,     $this->trunkBlock);
			$transaction->addBlockAt($x + 1, $y + $yy, $z,     $this->trunkBlock);
			$transaction->addBlockAt($x,     $y + $yy, $z + 1, $this->trunkBlock);
			$transaction->addBlockAt($x + 1, $y + $yy, $z + 1, $this->trunkBlock);
		}

		if($random->nextRange(0, 4) === 0){
			$dx = $random->nextRange(0, 1) === 0 ? -1 : 2; // extend left or right from the 2x2 trunk
			$dz = $random->nextRange(0, 1) === 0 ? -1 : 2; // extend north or south
			$branchY = $y + $height - $random->nextRange(1, 2);
			$transaction->addBlockAt($x + $dx, $branchY,     $z + $random->nextRange(0,1), $this->trunkBlock);
			$transaction->addBlockAt($x + $dx, $branchY + 1, $z + $random->nextRange(0,1), $this->trunkBlock);
		}
	}

	protected function placeCanopyLayer(BlockTransaction $transaction, Vector3 $center, int $radius, int $maxTaxicab) : void{
		$cx = $center->getFloorX();
		$cy = $center->getFloorY();
		$cz = $center->getFloorZ();

		for($xx = $cx - $radius; $xx <= $cx + $radius; $xx++){
			for($zz = $cz - $radius; $zz <= $cz + $radius; $zz++){
				$dx = abs($xx - $cx);
				$dz = abs($zz - $cz);
				if($dx + $dz <= $maxTaxicab){
					if($transaction->fetchBlockAt($xx, $cy, $zz)->canBeReplaced()){
						$transaction->addBlockAt($xx, $cy, $zz, $this->leafBlock);
					}
				}
			}
		}
	}

	protected function placeCanopy(int $x, int $y, int $z, Random $random, BlockTransaction $transaction) : void{
		$centerX = $x + 0.5;
		$centerZ = $z + 0.5;

		$topY = $y + 2 + $random->nextRange(0, 1);

		$mainCenter = new Vector3((int) floor($centerX), $topY,(int) floor($centerZ));

		$this->placeCanopyLayer($transaction, $mainCenter->up(), radius: 2, maxTaxicab: 2);
		$this->placeCanopyLayer($transaction, $mainCenter, radius: 3, maxTaxicab: 4);
		$this->placeCanopyLayer($transaction, $mainCenter->down(), radius: 3, maxTaxicab: 4);

		for($i = 0; $i < 8; $i++){
			$rx = $random->nextRange(-3, 4);
			$rz = $random->nextRange(-3, 4);
			$ry = $random->nextRange(-1, 1);
			$lx = (int) ($centerX + $rx);
			$ly = $topY + $ry;
			$lz = (int) ($centerZ + $rz);
			if($transaction->fetchBlockAt($lx, $ly, $lz)->canBeReplaced()){
				$transaction->addBlockAt($lx, $ly, $lz, $this->leafBlock);
			}
		}
	}
}
