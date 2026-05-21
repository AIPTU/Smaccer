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

namespace aiptu\smaccer\utils;

use aiptu\smaccer\Smaccer;
use pocketmine\utils\Filesystem;
use pocketmine\utils\Internet;
use function file_exists;
use function is_array;
use function is_string;
use function json_decode;
use function json_encode;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

class EmoteUtils {
	public const string CURRENT_COMMIT_URL = 'https://api.github.com/repos/TwistedAsylumMC/Bedrock-Emotes/commits/main';
	public const string EMOTES_URL = 'https://raw.githubusercontent.com/TwistedAsylumMC/Bedrock-Emotes/main/emotes.json';

	/**
	 * Retrieve the current commit ID from the Bedrock-Emotes repository.
	 */
	public static function getCurrentCommitId() : ?string {
		$response = Internet::getURL(self::CURRENT_COMMIT_URL);
		if ($response === null) {
			return null;
		}

		$data = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);

		if (!is_array($data) || !isset($data['sha']) || !is_string($data['sha'])) {
			return null;
		}

		return $data['sha'];
	}

	/**
	 * Retrieve and validate the list of emotes from the repository.
	 *
	 * @return list<array{uuid: string, title: string, image: string}>|null
	 */
	public static function getEmotes() : ?array {
		$response = Internet::getURL(self::EMOTES_URL);
		if ($response === null) {
			return null;
		}

		$data = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);

		if (!is_array($data) || !self::validateEmotesStructure($data)) {
			return null;
		}

		/** @var list<array{uuid: string, title: string, image: string}> $data */
		return $data;
	}

	/**
	 * Retrieve emotes from the cache file.
	 *
	 * @return array{commit_id: string, emotes: list<array{uuid: string, title: string, image: string}>}|null
	 */
	public static function getEmotesFromCache(string $cacheFilePath) : ?array {
		if (!file_exists($cacheFilePath)) {
			return null;
		}

		$content = Filesystem::fileGetContents($cacheFilePath);
		$data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

		if (!is_array($data) || !isset($data['commit_id'], $data['emotes'])) {
			return null;
		}

		if (!is_string($data['commit_id']) || !is_array($data['emotes'])) {
			return null;
		}

		if (!self::validateEmotesStructure($data['emotes'])) {
			return null;
		}

		/** @var array{commit_id: string, emotes: list<array{uuid: string, title: string, image: string}>} $data */
		return $data;
	}

	/**
	 * Save emotes to the cache file.
	 *
	 * @param list<array{uuid: string, title: string, image: string}> $emotes
	 */
	public static function saveEmoteToCache(string $cacheFilePath, string $commitId, array $emotes) : void {
		$jsonData = json_encode([
			'commit_id' => $commitId,
			'emotes' => $emotes,
		], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

		Filesystem::safeFilePutContents($cacheFilePath, $jsonData);
	}

	/**
	 * Get the emote cache file path.
	 */
	public static function getEmoteCachePath() : string {
		return Smaccer::getInstance()->getDataFolder() . 'emotes_cache.json';
	}

	/**
	 * Validate the structure of emotes data.
	 */
	private static function validateEmotesStructure(mixed $data) : bool {
		if (!is_array($data)) {
			return false;
		}

		foreach ($data as $emote) {
			if (!is_array($emote)) {
				return false;
			}

			if (
				!isset($emote['uuid'], $emote['title'], $emote['image'])
				|| !is_string($emote['uuid'])
				|| !is_string($emote['title'])
				|| !is_string($emote['image'])
			) {
				return false;
			}
		}

		return true;
	}
}