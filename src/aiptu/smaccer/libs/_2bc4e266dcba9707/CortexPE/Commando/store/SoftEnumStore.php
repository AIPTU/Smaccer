<?php

declare(strict_types=1);

namespace aiptu\smaccer\libs\_2bc4e266dcba9707\CortexPE\Commando\store;

use aiptu\smaccer\libs\_2bc4e266dcba9707\CortexPE\Commando\exception\CommandoException;
use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandSoftEnum;
use pocketmine\network\mcpe\protocol\UpdateSoftEnumPacket;
use pocketmine\Server;
use function array_values;

class SoftEnumStore {
	/** @var array<string, CommandSoftEnum> */
	private static array $enums = [];

	public static function getEnumByName(string $name) : ?CommandSoftEnum {
		return self::$enums[$name] ?? null;
	}

	/**
	 * @return array<string, CommandSoftEnum>
	 */
	public static function getEnums() : array {
		return self::$enums;
	}

	public static function addEnum(CommandSoftEnum $enum) : void {
		self::$enums[$enum->getName()] = $enum;
		self::broadcastSoftEnum($enum, UpdateSoftEnumPacket::TYPE_ADD);
	}

	public static function updateEnum(string $enumName, array $values) : void {
		if (self::getEnumByName($enumName) === null) {
			throw new CommandoException('Unknown enum named ' . $enumName);
		}

		$enum = self::$enums[$enumName] = new CommandSoftEnum($enumName, array_values($values));
		self::broadcastSoftEnum($enum, UpdateSoftEnumPacket::TYPE_SET);
	}

	public static function removeEnum(string $enumName) : void {
		if (($enum = self::getEnumByName($enumName)) === null) {
			throw new CommandoException('Unknown enum named ' . $enumName);
		}

		unset(self::$enums[$enumName]);
		self::broadcastSoftEnum($enum, UpdateSoftEnumPacket::TYPE_REMOVE);
	}

	public static function broadcastSoftEnum(CommandSoftEnum $enum, int $type) : void {
		$pk = new UpdateSoftEnumPacket();
		$pk->enumName = $enum->getName();
		$pk->values = $enum->getValues();
		$pk->type = $type;
		self::broadcastPacket($pk);
	}

	private static function broadcastPacket(ClientboundPacket $pk) : void {
		$sv = Server::getInstance();
		NetworkBroadcastUtils::broadcastPackets($sv->getOnlinePlayers(), [$pk]);
	}
}