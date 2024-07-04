<?php

/*
 * Copyright (c) 2024 AIPTU
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/AIPTU/Smaccer
 */

declare(strict_types=1);

namespace aiptu\smaccer\entity\trait;

use pocketmine\entity\Skin;

trait SkinTrait {
	public function changeSkin(string $skinData) : void {
		$this->setSkin(new Skin(
			$this->getSkin()->getSkinId(),
			$skinData,
			$this->getSkin()->getCapeData(),
			$this->getSkin()->getGeometryName(),
			$this->getSkin()->getGeometryData()
		));
		$this->sendSkin();
	}

	public function changeCape(string $capeData) : void {
		$this->setSkin(new Skin(
			$this->getSkin()->getSkinId(),
			$this->getSkin()->getSkinData(),
			$capeData,
			$this->getSkin()->getGeometryName(),
			$this->getSkin()->getGeometryData()
		));
		$this->sendSkin();
	}
}