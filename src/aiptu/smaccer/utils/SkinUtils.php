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

use aiptu\smaccer\Smaccer;
use aiptu\smaccer\utils\promise\Promise;
use aiptu\smaccer\utils\promise\PromiseResolver;
use GdImage;
use InvalidArgumentException;
use pocketmine\utils\Filesystem;
use RuntimeException;
use Symfony\Component\Filesystem\Path;
use Throwable;
use function chr;
use function imagecolorat;
use function imagecreatefrompng;
use function imagedestroy;
use function imageistruecolor;
use function imagepalettetotruecolor;
use function imagesx;
use function imagesy;
use function is_file;
use function uniqid;
use function unlink;

class SkinUtils {
	private const string SKIN = 'skin';
	private const string CAPE = 'cape';

	/**
	 * Downloads a skin from a URL and returns the skin bytes in a promise.
	 *
	 * @param string $url the URL of the PNG skin
	 *
	 * @return Promise<string> a promise that resolves to the skin bytes
	 *
	 * @throws InvalidArgumentException if the URL is invalid or not a PNG
	 */
	public static function skinFromURL(string $url) : Promise {
		$resolver = new PromiseResolver();

		try {
			self::validateUrl($url);
			self::validatePngUrl($url);

			Utils::fetchAsync($url, function ($result) use ($resolver) : void {
				if ($result === null) {
					$resolver->reject(new RuntimeException('Failed to download skin.'));
					return;
				}

				$skinData = $result->getBody();
				$filePath = self::saveSkinToFile($skinData);

				$skinBytes = self::skinFromFile($filePath);
				$resolver->resolve($skinBytes);
			});
		} catch (Throwable $e) {
			$resolver->reject($e);
		}

		return $resolver->getPromise();
	}

	/**
	 * Downloads a cape from a URL and returns the cape bytes in a promise.
	 *
	 * @param string $url the URL of the PNG cape
	 *
	 * @return Promise<string> a promise that resolves to the cape bytes
	 *
	 * @throws InvalidArgumentException if the URL is invalid or not a PNG
	 */
	public static function capeFromURL(string $url) : Promise {
		$resolver = new PromiseResolver();

		try {
			self::validateUrl($url);
			self::validatePngUrl($url);

			Utils::fetchAsync($url, function ($result) use ($resolver) : void {
				if ($result === null) {
					$resolver->reject(new RuntimeException('Failed to download cape.'));
					return;
				}

				$capeData = $result->getBody();
				$filePath = self::saveSkinToFile($capeData);

				$capeBytes = self::capeFromFile($filePath);
				$resolver->resolve($capeBytes);
			});
		} catch (Throwable $e) {
			$resolver->reject($e);
		}

		return $resolver->getPromise();
	}

	/**
	 * Processes a skin from a file path and returns the skin bytes.
	 *
	 * @param string $filePath the file path of the PNG skin
	 *
	 * @return string the skin bytes
	 *
	 * @throws RuntimeException if the file is not a valid PNG skin
	 */
	public static function skinFromFile(string $filePath) : string {
		return self::processPngFile($filePath, self::SKIN);
	}

	/**
	 * Processes a cape from a file path and returns the cape bytes.
	 *
	 * @param string $filePath the file path of the PNG cape
	 *
	 * @return string the cape bytes
	 *
	 * @throws RuntimeException if the file is not a valid PNG cape
	 */
	public static function capeFromFile(string $filePath) : string {
		return self::processPngFile($filePath, self::CAPE);
	}

	private static function processPngFile(string $filePath, string $type) : string {
		$image = imagecreatefrompng($filePath);
		if ($image === false) {
			self::cleanupFile($filePath);
			throw new RuntimeException("The file is not a valid PNG $type.");
		}

		if (!imageistruecolor($image)) {
			imagepalettetotruecolor($image);
		}

		$bytes = ($type === self::SKIN) ? self::extractSkinBytes($image) : self::extractCapeBytes($image);
		imagedestroy($image);
		self::cleanupFile($filePath);

		return $bytes;
	}

	/**
	 * Validates that a URL is in the correct format.
	 *
	 * @param string $url the URL to validate
	 *
	 * @throws InvalidArgumentException if the URL format is invalid
	 */
	private static function validateUrl(string $url) : void {
		if (!Utils::isValidUrl($url)) {
			throw new InvalidArgumentException('Invalid URL format.');
		}
	}

	/**
	 * Validates that a URL points to a PNG image.
	 *
	 * @param string $url the URL to validate
	 *
	 * @throws InvalidArgumentException if the URL does not point to a PNG image
	 */
	private static function validatePngUrl(string $url) : void {
		if (!Utils::isPngUrl($url)) {
			throw new InvalidArgumentException('URL does not point to a PNG image.');
		}
	}

	/**
	 * Saves image data to a temporary file.
	 *
	 * @param string $data the image data to save
	 *
	 * @return string the file path where the image data was saved
	 *
	 * @throws RuntimeException if there is an error saving the image data
	 */
	private static function saveSkinToFile(string $data) : string {
		$filePath = Path::join(Smaccer::getInstance()->getDataFolder(), uniqid('skin_', true) . '.png');
		try {
			Filesystem::safeFilePutContents($filePath, $data);
			return $filePath;
		} catch (RuntimeException $e) {
			throw new RuntimeException('An error occurred while saving the skin file: ' . $e->getMessage());
		}
	}

	/**
	 * Extracts the bytes from a GD image resource for skin.
	 *
	 * @param GdImage $image the GD image resource
	 *
	 * @return string the extracted skin bytes
	 */
	private static function extractSkinBytes(GdImage $image) : string {
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
	 * @param GdImage $image the GD image resource
	 *
	 * @return string the extracted cape bytes
	 */
	private static function extractCapeBytes(GdImage $image) : string {
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
