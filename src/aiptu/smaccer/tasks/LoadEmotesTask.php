<?php

/*
 * Copyright (c) 2024-2026 AIPTU
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/AIPTU/Smaccer
 */

declare(strict_types=1);

namespace aiptu\smaccer\tasks;

use aiptu\smaccer\entity\emote\EmoteManager;
use aiptu\smaccer\Smaccer;
use aiptu\smaccer\utils\EmoteUtils;
use pocketmine\command\CommandSender;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use function count;
use function is_array;
use function is_int;
use function is_string;

class LoadEmotesTask extends AsyncTask {
	private ?string $playerName;

	public function __construct(
		private string $cachedFilePath,
		private ?string $cachedCommitId,
		?CommandSender $sender = null
	) {
		$this->playerName = $sender?->getName();
	}

	public function onRun() : void {
		$currentCommitId = EmoteUtils::getCurrentCommitId();

		if ($currentCommitId === null) {
			$this->setResult([
				'status' => 'error',
				'message' => 'Failed to fetch current commit ID',
			]);
			return;
		}

		if ($this->cachedCommitId === $currentCommitId) {
			$this->setResult([
				'status' => 'unchanged',
				'message' => 'Emote cache is up-to-date',
			]);
			return;
		}

		$emotes = EmoteUtils::getEmotes();

		if ($emotes === null) {
			$this->setResult([
				'status' => 'error',
				'message' => 'Failed to fetch emotes from repository',
			]);
			return;
		}

		EmoteUtils::saveEmoteToCache($this->cachedFilePath, $currentCommitId, $emotes);

		$this->setResult([
			'status' => 'updated',
			'emotes' => $emotes,
			'commit_id' => $currentCommitId,
		]);
	}

	public function onCompletion() : void {
		$result = $this->getResult();

		if (!is_array($result) || !isset($result['status'])) {
			Smaccer::getInstance()->getLogger()->error('[Smaccer] Invalid emote task result');
			$this->sendMessage('§cAn unexpected error occurred while loading emotes.');
			return;
		}

		$status = $result['status'];
		$message = isset($result['message']) && is_string($result['message']) ? $result['message'] : 'Unknown error';

		if ($status === 'unchanged') {
			Smaccer::getInstance()->getLogger()->debug($message);
			$this->sendMessage('§aEmotes are already up-to-date!');
			return;
		}

		if ($status === 'error') {
			Smaccer::getInstance()->getLogger()->warning($message);
			$this->sendMessage('§cFailed to load emotes: §f' . $message);
			return;
		}

		if ($status === 'updated' && isset($result['emotes']) && is_array($result['emotes'])) {
			/** @var list<array{uuid: string, title: string, image: string}> $emotes */
			$emotes = $result['emotes'];

			$emoteManager = new EmoteManager($emotes);
			Smaccer::getInstance()->setEmoteManager($emoteManager);

			$count = (string) (isset($result['count']) && is_int($result['count']) ? $result['count'] : count($emotes));
			$commitId = isset($result['commit_id']) && is_string($result['commit_id']) ? $result['commit_id'] : 'unknown';

			Smaccer::getInstance()->getLogger()->info('Successfully loaded ' . $count . ' emotes (commit: ' . $commitId . ')');
			$this->sendMessage('§aSuccessfully loaded §e' . $count . '§a emotes!');
		}
	}

	/**
	 * Send message to the command sender who initiated the reload.
	 * If no sender (background update), message is not sent.
	 */
	private function sendMessage(string $message) : void {
		if ($this->playerName === null) {
			return;
		}

		$sender = Server::getInstance()->getPlayerExact($this->playerName);
		if ($sender !== null && $sender->isOnline()) {
			$sender->sendMessage($message);
		}
	}
}
