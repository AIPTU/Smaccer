<?php

/*
 * Copyright (c) 2024 AIPTU
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/AIPTU/Smaccer
 */

declare(strict_types=1);

namespace aiptu\smaccer\utils;

use aiptu\smaccer\entity\HumanSmaccer;
use aiptu\smaccer\Smaccer;
use pocketmine\entity\Skin;
use pocketmine\utils\Filesystem;
use Symfony\Component\Filesystem\Path;
use function chr;
use function imagecolorat;
use function imagecreatefrompng;
use function imagedestroy;
use function imagesx;
use function imagesy;
use function is_file;
use function uniqid;
use function unlink;

class SkinUtils {
	/**
	 * Downloads a skin from a URL and applies it to a HumanSmaccer entity.
	 *
	 * @param string       $url   the URL of the PNG skin
	 * @param HumanSmaccer $human the HumanSmaccer entity to apply the skin to
	 *
	 * @throws \InvalidArgumentException if the URL is invalid or not a PNG
	 * @throws \RuntimeException if there is an error downloading or processing the skin
	 */
	public static function skinFromURL(string $url, HumanSmaccer $human) : void {
		self::validateUrl($url);
		self::validatePngUrl($url);

		Utils::fetchAsync($url, function ($result) use ($human) : void {
			if ($result === null) {
				throw new \RuntimeException('Failed to download skin.');
			}

			$skinData = $result->getBody();
			$filePath = self::saveSkinToFile($skinData);

			$image = imagecreatefrompng($filePath);
			if ($image === false) {
				self::cleanupFile($filePath);
				throw new \RuntimeException('The file is not a valid PNG skin.');
			}

			$skinBytes = self::extractSkinBytes($image);
			imagedestroy($image);

			$human->changeSkin($skinBytes);

			self::cleanupFile($filePath);
		});
	}

	/**
	 * Downloads a cape from a URL and applies it to a HumanSmaccer entity.
	 *
	 * @param string       $url   the URL of the PNG cape
	 * @param HumanSmaccer $human the HumanSmaccer entity to apply the cape to
	 *
	 * @throws \InvalidArgumentException if the URL is invalid or not a PNG
	 * @throws \RuntimeException if there is an error downloading or processing the cape
	 */
	public static function capeFromURL(string $url, HumanSmaccer $human) : void {
		self::validateUrl($url);
		self::validatePngUrl($url);

		Utils::fetchAsync($url, function ($result) use ($human) : void {
			if ($result === null) {
				throw new \RuntimeException('Failed to download cape.');
			}

			$capeData = $result->getBody();
			$filePath = self::saveSkinToFile($capeData);

			$image = imagecreatefrompng($filePath);
			if ($image === false) {
				self::cleanupFile($filePath);
				throw new \RuntimeException('The file is not a valid PNG cape.');
			}

			$capeBytes = self::extractCapeBytes($image);
			imagedestroy($image);

			$human->changeCape($capeBytes);

			self::cleanupFile($filePath);
		});
	}

	/**
	 * Validates that a URL is in the correct format.
	 *
	 * @param string $url the URL to validate
	 *
	 * @throws \InvalidArgumentException if the URL format is invalid
	 */
	private static function validateUrl(string $url) : void {
		if (!Utils::isValidUrl($url)) {
			throw new \InvalidArgumentException('Invalid URL format.');
		}
	}

	/**
	 * Validates that a URL points to a PNG image.
	 *
	 * @param string $url the URL to validate
	 *
	 * @throws \InvalidArgumentException if the URL does not point to a PNG image
	 */
	private static function validatePngUrl(string $url) : void {
		if (!Utils::isPngUrl($url)) {
			throw new \InvalidArgumentException('URL does not point to a PNG image.');
		}
	}

	/**
	 * Saves image data to a temporary file.
	 *
	 * @param string $data the image data to save
	 *
	 * @return string the file path where the image data was saved
	 *
	 * @throws \RuntimeException if there is an error saving the image data
	 */
	private static function saveSkinToFile(string $data) : string {
		$filePath = Path::join(Smaccer::getInstance()->getDataFolder(), uniqid('skin_', true) . '.png');
		try {
			Filesystem::safeFilePutContents($filePath, $data);
			return $filePath;
		} catch (\RuntimeException $e) {
			throw new \RuntimeException('An error occurred while saving the skin file: ' . $e->getMessage());
		}
	}

	/**
	 * Extracts the bytes from a GD image resource for skin.
	 *
	 * @param \GdImage $image the GD image resource
	 *
	 * @return string the extracted skin bytes
	 */
	private static function extractSkinBytes(\GdImage $image) : string {
		$bytes = '';
		for ($y = 0; $y < imagesy($image); ++$y) {
			for ($x = 0; $x < imagesx($image); ++$x) {
				$rgba = imagecolorat($image, $x, $y);
				$a = ((~($rgba >> 24)) << 1) & 0xFF;
				$r = ($rgba >> 16) & 0xFF;
				$g = ($rgba >> 8) & 0xFF;
				$b = $rgba & 0xFF;
				$bytes .= chr($r) . chr($g) . chr($b) . chr($a);
			}
		}

		return $bytes;
	}

	/**
	 * Extracts the bytes from a GD image resource for cape.
	 *
	 * @param \GdImage $image the GD image resource
	 *
	 * @return string the extracted cape bytes
	 */
	private static function extractCapeBytes(\GdImage $image) : string {
		$bytes = '';
		for ($y = 0; $y < imagesy($image); ++$y) {
			for ($x = 0; $x < imagesx($image); ++$x) {
				$argb = imagecolorat($image, $x, $y);
				$bytes .= chr(($argb >> 16) & 0xFF) . chr(($argb >> 8) & 0xFF) . chr($argb & 0xFF) . chr(((~($argb >> 24)) << 1) & 0xFF);
			}
		}

		return $bytes;
	}

	/**
	 * Deletes a file if it exists.
	 *
	 * @param string $filePath the path to the file to delete
	 */
	private static function cleanupFile(string $filePath) : void {
		if (is_file($filePath)) {
			unlink($filePath);
		}
	}
}
