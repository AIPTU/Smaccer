<?php

declare(strict_types=1);

namespace aiptu\smaccer\entity;

use aiptu\smaccer\entity\trait\SmaccerTrait;
use aiptu\smaccer\entity\utils\EntityTag;
use pocketmine\entity\animation\ArmSwingAnimation;
use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;

class HumanSmaccer extends Human {
	use SmaccerTrait;

	protected bool $slapBack = true;

	public function __construct(Location $location, Skin $skin, ?CompoundTag $nbt = null) {
		if ($nbt instanceof CompoundTag) {
			$this->creator = $nbt->getString(EntityTag::CREATOR);
		}

		parent::__construct($location, $skin, $nbt);
	}

	protected function initEntity(CompoundTag $nbt) : void {
		parent::initEntity($nbt);

		$this->setSlapBack((bool) $nbt->getByte(EntityTag::SLAP_BACK, (int) $this->slapBack));
	}

	public function saveNBT() : CompoundTag {
		$nbt = parent::saveNBT();

		$nbt->setByte(EntityTag::SLAP_BACK, (int) $this->slapBack);
		return $nbt;
	}

	public function onInteract(Player $player, Vector3 $clickPos) : bool {
		parent::onInteract($player, $clickPos);

		if ($this->canSlapBack()) {
			$this->broadcastAnimation(new ArmSwingAnimation($this), $this->getViewers());
		}

		return true;
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

	public function setSlapBack(bool $value = true) : void {
		$this->slapBack = $value;
	}

	public function canSlapBack() : bool {
		return $this->slapBack;
	}
}
