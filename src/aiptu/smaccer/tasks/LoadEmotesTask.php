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

namespace aiptu\smaccer\tasks;

use aiptu\smaccer\entity\emote\EmoteManager;
use aiptu\smaccer\Smaccer;
use aiptu\smaccer\utils\EmoteUtils;
use pocketmine\scheduler\AsyncTask;
use RuntimeException;
use function is_array;

class LoadEmotesTask extends AsyncTask {
	public function __construct(
		private string $cachedFilePath
	) {}

	public function onRun() : void {
		$currentCommitId = EmoteUtils::getCurrentCommitId();
		$cachedFile = EmoteUtils::getEmotesFromCache($this->cachedFilePath);

		if ($currentCommitId === null) {
			throw new RuntimeException('Failed to fetch current commit ID');
		}

		if ($cachedFile === null || $cachedFile['commit_id'] !== $currentCommitId) {
			$emotes = EmoteUtils::getEmotes();
			if ($emotes === null) {
				throw new RuntimeException('Failed to fetch emote list');
			}

			EmoteUtils::saveEmoteToCache($this->cachedFilePath, $currentCommitId, $emotes);

			$this->setResult($emotes);
			return;
		}

		$this->setResult($cachedFile['emotes']);
	}

	public function onCompletion() : void {
		/** @var array{array{uuid: string, title: string, image: string}} $result */
		$result = $this->getResult();
		if (!is_array($result)) {
			throw new RuntimeException('Emotes result is not an array');
		}

		Smaccer::getInstance()->setEmoteManager(new EmoteManager($result));
	}
}
