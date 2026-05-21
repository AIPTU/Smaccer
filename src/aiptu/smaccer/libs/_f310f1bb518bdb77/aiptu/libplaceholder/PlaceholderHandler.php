<?php

/*
 * Copyright (c) 2024 - 2025 AIPTU
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/AIPTU/libplaceholder
 */

declare(strict_types=1);

namespace aiptu\smaccer\libs\_f310f1bb518bdb77\aiptu\libplaceholder;

interface PlaceholderHandler {
	public function handle(string $placeholder, PlaceholderContext $context, string ...$args) : string;
}