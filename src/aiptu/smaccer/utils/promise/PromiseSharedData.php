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

namespace aiptu\smaccer\utils\promise;

use Closure;
use Throwable;

/**
 * @internal
 *
 * @see PromiseResolver
 *
 * @phpstan-template TValue
 */
final class PromiseSharedData {
	/**
	 * An array of {@see Closure}s to call when the promise is resolved successfully.
	 *
	 * @phpstan-var array<int, (Closure(TValue): void)|(Closure(): void)>
	 */
	public array $onSuccess = [];
	/**
	 * An array of {@see Closure}s to call when the promise is rejected.
	 *
	 * @phpstan-var array<int, (Closure(Throwable): void)|(Closure(): void)>
	 */
	public array $onError = [];

	/**
	 * The result of the promise.
	 *
	 * @phpstan-var TValue|null
	 */
	public mixed $result = null;
	/** The exception that was thrown when the promise was rejected. */
	public ?Throwable $error = null;
}