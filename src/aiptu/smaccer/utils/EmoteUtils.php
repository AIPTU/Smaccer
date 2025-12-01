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
	public const CURRENT_COMMIT_URL = 'https://api.github.com/repos/TwistedAsylumMC/Bedrock-Emotes/commits/main';
	public const EMOTES_URL = 'https://raw.githubusercontent.com/TwistedAsylumMC/Bedrock-Emotes/main/emotes.json';

	/**
	 * Retrieve the current commit ID from https://github.com/TwistedAsylumMC/Bedrock-Emotes.
	 *
	 * @return string|null a `string` of current commit id or `null` if there is an issue with fetching the current commit ID
	 */
	public static function getCurrentCommitId() : ?string {
		$response = Internet::getURL(self::CURRENT_COMMIT_URL);
		if ($response === null) {
			return null;
		}

		$data = json_decode($response->getBody(), true, flags: JSON_THROW_ON_ERROR);
		if (!is_array($data) || !isset($data['sha']) || !is_string($data['sha'])) {
			return null;
		}

		return $data['sha'];
	}

	/**
	 * Retrieve a list of emotes in emotes.json from this github repository https://github.com/TwistedAsylumMC/Bedrock-Emotes.
	 *
	 * @return array{
	 *      array{
	 *          uuid: string,
	 *          title: string,
	 *          image: string
	 *      }
	 * }|null An array of associative arrays, each containing:
	 *               - 'uuid' (string): The unique identifier of the emote.
	 *               - 'title' (string): The title of the emote.
	 *               - 'image' (string): The URL to the thumbnail image of the emote.
	 *
	 *               Or `null` if there is an issue with fetching the emotes.
	 */
	public static function getEmotes() : ?array {
		$response = Internet::getURL(self::EMOTES_URL);
		if ($response === null) {
			return null;
		}

		/** @var array{array{uuid: string, title: string, image: string}} $data */
		$data = json_decode($response->getBody(), true, flags: JSON_THROW_ON_ERROR);
		if (!is_array($data)) {
			return null;
		}

		foreach ($data as $emote) {
			if (!isset($emote['uuid'], $emote['title'], $emote['image']) || !is_string($emote['uuid']) || !is_string($emote['title']) || !is_string($emote['image'])) {
				return null;
			}
		}

		return $data;
	}

	/**
	 * Retrieve emotes from a cache file.
	 *
	 * @param string $cacheFilePath the path to the cache file
	 *
	 * @return array{
	 *      commit_id: string,
	 *      emotes: array
	 * }|null Returns an associative array with `commit_id` and `emotes` if the cache file exists,
	 *        or `null` if the file does not exist
	 */
	public static function getEmotesFromCache(string $cacheFilePath) : ?array {
		if (file_exists($cacheFilePath)) {
			$data = json_decode(Filesystem::fileGetContents($cacheFilePath), true, flags: JSON_THROW_ON_ERROR);
			if (!is_array($data) || !isset($data['commit_id'], $data['emotes']) || !is_string($data['commit_id']) || !is_array($data['emotes'])) {
				return null;
			}

			foreach ($data['emotes'] as $emote) {
				if (!isset($emote['uuid'], $emote['title'], $emote['image']) || !is_string($emote['uuid']) || !is_string($emote['title']) || !is_string($emote['image'])) {
					return null;
				}
			}

			return $data;
		}

		return null;
	}

	/**
	 * Save emotes to a cache file.
	 *
	 * @param string $cacheFilePath the path to the cache file will be saved
	 * @param string $commitId      the Current Commit ID
	 * @param array{
	 *      array{
	 *          uuid: string,
	 *          title: string,
	 *          image: string
	 *      }
	 * } $emotes the array of emotes list
	 */
	public static function saveEmoteToCache(string $cacheFilePath, string $commitId, array $emotes) : void {
		$jsonData = json_encode([
			'commit_id' => $commitId,
			'emotes' => $emotes,
		], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
		if ($jsonData === false) {
			throw new \RuntimeException('Failed to encode emotes to JSON.');
		}

		Filesystem::safeFilePutContents($cacheFilePath, $jsonData);
	}

	/**
	 * Get the emote file path.
	 *
	 * @return string the emote file path
	 */
	public static function getEmoteCachePath() : string {
		return Smaccer::getInstance()->getDataFolder() . 'emotes_cache.json';
	}
}
