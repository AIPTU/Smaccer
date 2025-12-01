<?php

/*
 * Copyright (c) 2024-2025 AIPTU
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/AIPTU/Smaccer
 */

declare(strict_types=1);

namespace aiptu\smaccer\entity\trait;

use pocketmine\item\Item;
use pocketmine\player\Player;

trait InventoryTrait {
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
}
