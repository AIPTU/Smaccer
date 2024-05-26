<?php

declare(strict_types=1);

namespace aiptu\smaccer\entity;

use pocketmine\entity\Entity;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use function count;

class NPCHandler {
	use SingletonTrait;

	/** @var array<string, array<int, Entity>> */
	private array $playerNPCs = [];

	public function addNPC(Player $player, Entity $npc) : void {
		$playerId = $player->getUniqueId()->getBytes();
		$this->playerNPCs[$playerId][$npc->getId()] = $npc;
	}

	public function removeNPC(Player $player, int $npcId) : void {
		$playerId = $player->getUniqueId()->getBytes();
		unset($this->playerNPCs[$playerId][$npcId]);

		// Remove player entry if it has no NPCs left
		if (!$this->playerHasNPCs($playerId)) {
			unset($this->playerNPCs[$playerId]);
		}
	}

	public function getNPCById(Player $player, int $npcId) : ?Entity {
		$playerId = $player->getUniqueId()->getBytes();
		return $this->playerNPCs[$playerId][$npcId] ?? null;
	}

	public function getNPCsByPlayer(Player $player) : array {
		$playerId = $player->getUniqueId()->getBytes();
		return $this->playerNPCs[$playerId] ?? [];
	}

	public function getAllNPCs() : array {
		return $this->playerNPCs;
	}

	public function getNPCByIdAndCreator(string $creatorId, int $npcId) : ?Entity {
		return $this->playerNPCs[$creatorId][$npcId] ?? null;
	}

	public function getNPCsByCreator(string $creatorId) : array {
		return $this->playerNPCs[$creatorId] ?? [];
	}

	private function playerHasNPCs(string $playerId) : bool {
		return isset($this->playerNPCs[$playerId]) && count($this->playerNPCs[$playerId]) > 0;
	}
}
