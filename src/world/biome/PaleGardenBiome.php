<?php

declare(strict_types=1);

namespace pocketmine\world\biome;

use pocketmine\world\generator\populator\TallGrass;
use pocketmine\world\generator\populator\Tree;
use pocketmine\world\generator\object\TreeType;

class PaleGardenBiome extends GrassyBiome{

    public function __construct(){
        parent::__construct();

        $tallGrass = new TallGrass();
        $tallGrass->setBaseAmount(6);
        $this->addPopulator($tallGrass);

        $trees = new Tree(TreeType::PALE_OAK);
        $trees->setBaseAmount(6);
        $this->addPopulator($trees);

        $this->setElevation(60, 70);

        $this->temperature = 0.4;
        $this->rainfall = 0.3;
    }

    public function getName(): string{
        return "Pale Garden";
    }
}