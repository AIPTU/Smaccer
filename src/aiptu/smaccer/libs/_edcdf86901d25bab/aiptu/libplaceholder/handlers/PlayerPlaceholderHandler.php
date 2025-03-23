<?php

/*
 * Copyright (c) 2024 AIPTU
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/AIPTU/libplaceholder
 */

declare(strict_types=1);

namespace aiptu\smaccer\libs\_edcdf86901d25bab\aiptu\libplaceholder\handlers;

use aiptu\smaccer\libs\_edcdf86901d25bab\aiptu\libplaceholder\PlaceholderContext;
use aiptu\smaccer\libs\_edcdf86901d25bab\aiptu\libplaceholder\PlaceholderHandler;
use pocketmine\player\Player;
use function round;

class PlayerPlaceholderHandler implements PlaceholderHandler {
	public function handle(string $placeholder, PlaceholderContext $context, ...$args) : string {
		$player = $context->getPlayer();
		if (!$player instanceof Player) {
			return 'N/A';
		}

		switch ($placeholder) {
			case 'name':
				return $player->getName();
			case 'display_name':
				return $player->getDisplayName();
			case 'health':
				return (string) round($player->getHealth(), 2);
			case 'max_health':
				return (string) $player->getMaxHealth();
			case 'x':
				return (string) $player->getPosition()->getFloorX();
			case 'y':
				return (string) $player->getPosition()->getFloorY();
			case 'z':
				return (string) $player->getPosition()->getFloorZ();
			case 'world':
				return $player->getLocation()->isValid() ? $player->getWorld()->getDisplayName() : 'Unknown';
			case 'ip':
				return $player->getNetworkSession()->getIp();
			case 'gamemode':
				return $player->getGamemode()->getEnglishName();
			case 'ping':
				return (string) $player->getNetworkSession()->getPing();
			default:
				return '{' . $placeholder . '}';
		}
	}
}