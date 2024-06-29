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

namespace aiptu\smaccer\entity;

use aiptu\smaccer\entity\trait\CommandTrait;
use aiptu\smaccer\entity\trait\CreatorTrait;
use aiptu\smaccer\entity\trait\EmoteTrait;
use aiptu\smaccer\entity\trait\RotationTrait;
use aiptu\smaccer\entity\trait\SlapBackTrait;
use aiptu\smaccer\entity\trait\VisibilityTrait;
use aiptu\smaccer\entity\utils\EntityTag;
use aiptu\smaccer\Smaccer;
use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;

class HumanSmaccer extends Human {
	use CreatorTrait;
	use RotationTrait;
	use VisibilityTrait;
	use SlapBackTrait;
	use EmoteTrait;
	use CommandTrait;

	public function __construct(Location $location, Skin $skin, ?CompoundTag $nbt = null) {
		if ($nbt instanceof CompoundTag) {
			$this->initializeCreator($nbt);
			$this->initializeCommand($nbt);
		}

		parent::__construct($location, $skin, $nbt);
	}

	protected function initEntity(CompoundTag $nbt) : void {
		parent::initEntity($nbt);

		$this->setScale($nbt->getFloat(EntityTag::SCALE, 1.0));
		$this->initializeRotation($nbt);
		$this->setNameTagAlwaysVisible((bool) $nbt->getByte(EntityTag::NAMETAG_VISIBLE, 1));
		$this->setNameTagVisible((bool) $nbt->getByte(EntityTag::NAMETAG_VISIBLE, 1));
		$this->initializeVisibility($nbt);
		$this->initializeSlapBack($nbt);
		$this->initializeEmote($nbt);
	}

	public function saveNBT() : CompoundTag {
		$nbt = parent::saveNBT();

		$this->saveCreator($nbt);
		$nbt->setFloat(EntityTag::SCALE, $this->scale);
		$this->saveRotation($nbt);
		$nbt->setByte(EntityTag::NAMETAG_VISIBLE, (int) $this->isNameTagVisible());
		$this->saveVisibility($nbt);
		$this->saveEmote($nbt);
		$this->saveSlapBack($nbt);
		$this->saveCommand($nbt);

		return $nbt;
	}

	public function getName() : string {
		return $this->nameTag !== '' ? $this->nameTag : 'Human';
	}

	protected function entityBaseTick(int $tickDiff = 1) : bool {
		$hasUpdate = parent::entityBaseTick($tickDiff);

		if ($this->emote !== null) {
			$emoteUuid = $this->emote->getUuid();

			if (Smaccer::getInstance()->getDefaultSettings()->isEmoteCooldownEnabled()) {
				if ($this->handleEmoteCooldown($emoteUuid)) {
					$this->broadcastEmote($emoteUuid);
					$hasUpdate = true;
				}
			} else {
				$this->broadcastEmote($emoteUuid);
				$hasUpdate = true;
			}
		}

		return $hasUpdate;
	}

	public function setArmor(Player $source) : void {
		$armorContents = $source->getArmorInventory()->getContents();
		$this->getArmorInventory()->setContents($armorContents);
	}

	public function setHelmet(Item|Player $source) : void {
		$item = $source instanceof Player ? $source->getArmorInventory()->getHelmet() : $source;
		$this->getArmorInventory()->setHelmet($item);
	}

	public function setChestplate(Item|Player $source) : void {
		$item = $source instanceof Player ? $source->getArmorInventory()->getChestplate() : $source;
		$this->getArmorInventory()->setChestplate($item);
	}

	public function setLeggings(Item|Player $source) : void {
		$item = $source instanceof Player ? $source->getArmorInventory()->getLeggings() : $source;
		$this->getArmorInventory()->setLeggings($item);
	}

	public function setBoots(Item|Player $source) : void {
		$item = $source instanceof Player ? $source->getArmorInventory()->getBoots() : $source;
		$this->getArmorInventory()->setBoots($item);
	}

	public function setOffHandItem(Item|Player $source) : void {
		$offHandItem = $source instanceof Player ? $source->getOffHandInventory()->getItem(0) : $source;
		$this->getOffHandInventory()->setItem(0, $offHandItem);
	}

	public function setItemInHand(Item|Player $source) : void {
		$itemInHand = $source instanceof Player ? $source->getInventory()->getItemInHand() : $source;
		$this->getInventory()->setItemInHand($itemInHand);
	}

	public function getArmor() : array {
		return $this->getArmorInventory()->getContents();
	}

	public function getHelmet() : Item {
		return $this->getArmorInventory()->getHelmet();
	}

	public function getChestplate() : Item {
		return $this->getArmorInventory()->getChestplate();
	}

	public function getLeggings() : Item {
		return $this->getArmorInventory()->getLeggings();
	}

	public function getBoots() : Item {
		return $this->getArmorInventory()->getBoots();
	}

	public function getOffHandItem() : Item {
		return $this->getOffHandInventory()->getItem(0);
	}

	public function getItemInHand() : Item {
		return $this->getInventory()->getItemInHand();
	}

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
