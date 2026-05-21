<?php

/*
 * Copyright (c) 2024 - 2025 AIPTU
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/AIPTU/libplaceholder
 */

declare(strict_types=1);

namespace aiptu\smaccer\libs\_f310f1bb518bdb77\aiptu\libplaceholder\handlers;

use aiptu\smaccer\libs\_f310f1bb518bdb77\aiptu\libplaceholder\PlaceholderContext;
use aiptu\smaccer\libs\_f310f1bb518bdb77\aiptu\libplaceholder\PlaceholderHandler;
use pocketmine\player\Player;
use function number_format;
use function round;

final class PlayerPlaceholderHandler implements PlaceholderHandler {
	private const string NO_PLAYER_FALLBACK = 'N/A';

	public function handle(string $placeholder, PlaceholderContext $context, string ...$args) : string {
		$player = $context->getPlayer();

		if (!$player instanceof Player || !$player->isConnected()) {
			return self::NO_PLAYER_FALLBACK;
		}

		return match ($placeholder) {
			'name' => $player->getName(),
			'display_name' => $player->getDisplayName(),
			'health' => self::formatNumber($player->getHealth(), 2),
			'max_health' => self::formatNumber($player->getMaxHealth(), 0),
			'health_percentage' => self::formatNumber(
				($player->getHealth() / $player->getMaxHealth()) * 100,
				1
			),
			'x' => (string) $player->getPosition()->getFloorX(),
			'y' => (string) $player->getPosition()->getFloorY(),
			'z' => (string) $player->getPosition()->getFloorZ(),
			'world' => $player->getLocation()->isValid()
				? $player->getWorld()->getDisplayName()
				: 'Unknown',
			'world_folder' => $player->getLocation()->isValid()
				? $player->getWorld()->getFolderName()
				: 'Unknown',
			'ip' => $player->getNetworkSession()->getIp(),
			'port' => (string) $player->getNetworkSession()->getPort(),
			'ping' => (string) $player->getNetworkSession()->getPing(),
			'gamemode' => $player->getGamemode()->getEnglishName(),
			'gamemode_id' => (string) $player->getGamemode()->id(),
			'food' => (string) $player->getHungerManager()->getFood(),
			'max_food' => (string) $player->getHungerManager()->getMaxFood(),
			'saturation' => self::formatNumber($player->getHungerManager()->getSaturation(), 2),
			'xp_level' => (string) $player->getXpManager()->getXpLevel(),
			'xp_progress' => self::formatNumber($player->getXpManager()->getXpProgress() * 100, 1),
			default => '{' . $placeholder . '}'
		};
	}

	private static function formatNumber(float|int $value, int $precision) : string {
		return $precision > 0
			? number_format($value, $precision, '.', '')
			: (string) (int) round($value);
	}
}