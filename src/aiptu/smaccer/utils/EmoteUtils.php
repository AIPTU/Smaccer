<?php

namespace aiptu\smaccer\utils;

use aiptu\smaccer\Smaccer;
use pocketmine\utils\Internet;
use pocketmine\utils\InternetException;

class EmoteUtils
{
    const CURRENT_COMMIT_URL = "https://api.github.com/repos/TwistedAsylumMC/Bedrock-Emotes/commits/main";
    const EMOTES_URL = "https://raw.githubusercontent.com/TwistedAsylumMC/Bedrock-Emotes/main/emotes.json";

    /**
     * Retreive the current commit ID from https://github.com/TwistedAsylumMC/Bedrock-Emotes.
     *
     * @return string|InternetException a `string` of current commit id or an `InternetException` If there is an issue with fetching the current commit ID
     */
    public static function getCurrentCommitId(): ?string
    {
        try {
            $response = Internet::simpleCurl(self::CURRENT_COMMIT_URL);
            return json_decode($response->getBody(), true)["sha"] ?? "";
        } catch (InternetException $e) {
            return $e;
        }
    }

    /**
     * Retreive a list of emotes in emotes.json from this github repository https://github.com/TwistedAsylumMC/Bedrock-Emotes.
     *
     * @return array{
     *      array{
     *          uuid: string,
     *          title: string,
     *          image: string
     *      }
     * }|InternetException An array of associative arrays, each containing:
     *               - 'uuid' (string): The unique identifier of the emote.
     *               - 'title' (string): The title of the emote.
     *               - 'image' (string): The URL to the thumbnail image of the emote.
     *
     *               Or an `InternetException` If there is an issue with fetching the emotes.
     */
    public static function getEmotes()
    {
        try {
            $response = Internet::simpleCurl(self::EMOTES_URL);
            return json_decode($response->getBody(), true);
        } catch (InternetException $e) {
            return $e;
        }
    }

    /**
     * Retreive emotes from a cache file
     *
     * @param string $cacheFilePath the path to the cache file.
     *
     * @return array{
     *      commit_id: string,
     *      emotes: array
     * }|null Returns an associative array with `commit_id` and `emotes` if the cache file exists,
     *        or `null` if the file does not exist.
     */
    public static function getEmotesFromCache(string $cacheFilePath): ?array
    {
        if (file_exists($cacheFilePath)) {
            return json_decode(file_get_contents($cacheFilePath), true);
        }

        return null;
    }

    /**
     * Save emotes to a cache file
     * 
     * @param string $cacheFilePath the path to the cache file will be saved
     * @param string $commitId      the Current Commit ID
     * @param array  $emotes        the array of emotes
     */
    public static function saveEmoteToCache(string $cacheFilePath, string $commitId, array $emotes)
    {
        file_put_contents($cacheFilePath, json_encode([
            "commit_id" => $commitId,
            "emotes" => $emotes,
        ], JSON_PRETTY_PRINT));
    }

    /**
     * Get the emote file path
     * 
     * @return string the emote file path
     */
    public static function getEmoteCachePath(): string
    {
        return Smaccer::getInstance()->getDataFolder() . "emotes_cache.json";
    }
}
