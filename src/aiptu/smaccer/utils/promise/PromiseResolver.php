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

use LogicException;
use Throwable;

/**
 * @phpstan-template TValue
 */
final class PromiseResolver {
	/** @phpstan-var PromiseSharedData<TValue> */
	private PromiseSharedData $shared;
	/** @phpstan-var Promise<TValue> */
	private Promise $promise;

	public function __construct() {
		$this->shared = new PromiseSharedData();
		$this->promise = new Promise($this->shared);
	}

	/**
	 * Resolves the promise with the given value.
	 *
	 * @param mixed $value the value to resolve the promise with
	 *
	 * @phpstan-param TValue $value
	 *
	 * @throws LogicException when the promise has already been resolved or rejected
	 */
	public function resolve(mixed $value) : void {
		if ($this->promise->isResolved()) {
			throw new LogicException('Promise has already been ' . ($this->shared->result === null ? 'rejected' : 'resolved'));
		}

		$this->shared->result = $value;
		foreach ($this->shared->onSuccess as $closure) {
			$closure($value);
		}

		$this->shared->onSuccess = [];
		$this->shared->onError = [];
	}

	/**
	 * Resolves the promise with the given value. Unlike {@see PromiseResolver::resolve()}, this method does not throw
	 * an exception if the promise has already been resolved or rejected.
	 *
	 * @param mixed $value the value to resolve the promise with
	 *
	 * @phpstan-param TValue $value
	 * Returns true if the promise was successfully resolved, or false if it was already resolved or rejected.
	 */
	public function resolveSilent(mixed $value) : bool {
		try {
			$this->resolve($value);
		} catch (LogicException) {
			return false;
		}

		return true;
	}

	/**
	 * Rejects the promise with the given exception.
	 *
	 * @param Throwable $error the exception to reject the promise with
	 *
	 * @throws LogicException when the promise has already been resolved or rejected
	 */
	public function reject(Throwable $error) : void {
		if ($this->promise->isResolved()) {
			throw new LogicException('Promise has already been ' . ($this->shared->result === null ? 'rejected' : 'resolved'));
		}

		$this->shared->error = $error;
		foreach ($this->shared->onError as $closure) {
			$closure($error);
		}

		$this->shared->onSuccess = [];
		$this->shared->onError = [];
	}

	/**
	 * Rejects the promise with the given exception. Unlike {@see PromiseResolver::reject()}, this method does not throw
	 * an exception if the promise has already been resolved or rejected.
	 *
	 * @param Throwable $error The exception to reject the promise with.
	 *                         Returns true if the promise was successfully rejected, or false if it was already resolved or rejected.
	 */
	public function rejectSilent(Throwable $error) : bool {
		try {
			$this->reject($error);
		} catch (LogicException) {
			return false;
		}

		return true;
	}

	/**
	 * @phpstan-return Promise<TValue>
	 */
	public function getPromise() : Promise {
		return $this->promise;
	}
}