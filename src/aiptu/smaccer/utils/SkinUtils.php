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
use aiptu\smaccer\utils\promise\Promise;
use aiptu\smaccer\utils\promise\PromiseResolver;
use GdImage;
use InvalidArgumentException;
use pocketmine\utils\Filesystem;
use pocketmine\utils\InternetRequestResult;
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

final class SkinUtils {
	private const string TYPE_SKIN = 'skin';
	private const string TYPE_CAPE = 'cape';

	private function __construct() {}

	/**
	 * Download and process skin from URL.
	 *
	 * @param string $url PNG image URL
	 *
	 * @phpstan-return Promise<string> Resolves to RGBA bytes (64x64 or 64x32)
	 *
	 * @throws InvalidArgumentException if URL format invalid
	 */
	public static function skinFromURL(string $url) : Promise {
		self::validatePngUrl($url);
		return self::downloadAndProcess($url, self::TYPE_SKIN);
	}

	/**
	 * Download and process cape from URL.
	 *
	 * @param string $url PNG image URL
	 *
	 * @phpstan-return Promise<string> Resolves to RGBA bytes (typically 64x32)
	 *
	 * @throws InvalidArgumentException if URL format invalid
	 */
	public static function capeFromURL(string $url) : Promise {
		self::validatePngUrl($url);
		return self::downloadAndProcess($url, self::TYPE_CAPE);
	}

	/**
	 * Process skin from local file.
	 *
	 * @param string $filePath Path to PNG file
	 *
	 * @return string RGBA bytes
	 *
	 * @throws RuntimeException if file invalid or processing fails
	 */
	public static function skinFromFile(string $filePath) : string {
		return self::processPngFile($filePath, self::TYPE_SKIN);
	}

	/**
	 * Process cape from local file.
	 *
	 * @param string $filePath Path to PNG file
	 *
	 * @return string RGBA bytes
	 *
	 * @throws RuntimeException if file invalid or processing fails
	 */
	public static function capeFromFile(string $filePath) : string {
		return self::processPngFile($filePath, self::TYPE_CAPE);
	}

	/**
	 * @phpstan-param self::TYPE_* $type
	 *
	 * @phpstan-return Promise<string>
	 */
	private static function downloadAndProcess(string $url, string $type) : Promise {
		/** @phpstan-var PromiseResolver<string> $resolver */
		$resolver = new PromiseResolver();

		Utils::fetchAsync($url, static function (?InternetRequestResult $result) use ($resolver, $type) : void {
			if ($result === null) {
				$resolver->reject(new RuntimeException('Failed to download image from URL'));
				return;
			}

			try {
				$imageData = $result->getBody();
				$tempPath = self::saveTempFile($imageData);

				$bytes = self::processPngFile($tempPath, $type);
				$resolver->resolve($bytes);
			} catch (Throwable $e) {
				$resolver->reject($e);
			}
		});

		return $resolver->getPromise();
	}

	/**
	 * Process PNG file to RGBA bytes.
	 *
	 * @phpstan-param self::TYPE_* $type
	 *
	 * @throws RuntimeException on invalid PNG or processing error
	 */
	private static function processPngFile(string $filePath, string $type) : string {
		$image = @imagecreatefrompng($filePath);

		if ($image === false) {
			self::cleanupFile($filePath);
			throw new RuntimeException("Invalid PNG {$type} file");
		}

		// Convert palette images to truecolor
		if (!imageistruecolor($image)) {
			imagepalettetotruecolor($image);
		}

		try {
			$bytes = match ($type) {
				self::TYPE_SKIN => self::extractSkinBytes($image),
				self::TYPE_CAPE => self::extractCapeBytes($image),
				default => throw new RuntimeException("Unknown type: {$type}")
			};
		} finally {
			imagedestroy($image);
			self::cleanupFile($filePath);
		}

		return $bytes;
	}

	/**
	 * Extract RGBA bytes from skin image.
	 *
	 * Format: 4 bytes per pixel (R, G, B, A), row-major order.
	 *
	 * @param GdImage $image Source image resource
	 *
	 * @return string RGBA byte string
	 */
	private static function extractSkinBytes(GdImage $image) : string {
		$bytes = '';
		$width = imagesx($image);
		$height = imagesy($image);

		for ($y = 0; $y < $height; ++$y) {
			for ($x = 0; $x < $width; ++$x) {
				$rgba = imagecolorat($image, $x, $y);

				$r = ($rgba >> 16) & 0xFF;
				$g = ($rgba >> 8) & 0xFF;
				$b = $rgba & 0xFF;
				$a = ((~($rgba >> 24)) << 1) & 0xFF; // Convert 7-bit alpha to 8-bit

				$bytes .= chr($r) . chr($g) . chr($b) . chr($a);
			}
		}

		return $bytes;
	}

	/**
	 * Extract RGBA bytes from cape image.
	 *
	 * @param GdImage $image Source image resource
	 *
	 * @return string RGBA byte string
	 */
	private static function extractCapeBytes(GdImage $image) : string {
		$bytes = '';
		$width = imagesx($image);
		$height = imagesy($image);

		for ($y = 0; $y < $height; ++$y) {
			for ($x = 0; $x < $width; ++$x) {
				$argb = imagecolorat($image, $x, $y);

				$r = ($argb >> 16) & 0xFF;
				$g = ($argb >> 8) & 0xFF;
				$b = $argb & 0xFF;
				$a = ((~($argb >> 24)) << 1) & 0xFF;

				$bytes .= chr($r) . chr($g) . chr($b) . chr($a);
			}
		}

		return $bytes;
	}

	/**
	 * Validate URL format and PNG extension.
	 *
	 * @throws InvalidArgumentException if validation fails
	 */
	private static function validatePngUrl(string $url) : void {
		if (!Utils::isValidUrl($url)) {
			throw new InvalidArgumentException('Invalid URL format');
		}

		if (!Utils::isPngUrl($url)) {
			throw new InvalidArgumentException('URL must point to PNG image');
		}
	}

	/**
	 * Save image data to temporary file.
	 *
	 * @param string $data Image bytes
	 *
	 * @return string Path to saved file
	 *
	 * @throws RuntimeException on write failure
	 */
	private static function saveTempFile(string $data) : string {
		$filePath = Path::join(
			Smaccer::getInstance()->getDataFolder(),
			uniqid('skin_', true) . '.png'
		);

		try {
			Filesystem::safeFilePutContents($filePath, $data);
			return $filePath;
		} catch (RuntimeException $e) {
			throw new RuntimeException('Failed to save temporary file: ' . $e->getMessage(), 0, $e);
		}
	}

	/**
	 * Delete temporary file if exists.
	 *
	 * @param string $filePath Path to file
	 */
	private static function cleanupFile(string $filePath) : void {
		if (is_file($filePath)) {
			@unlink($filePath);
		}
	}
}
