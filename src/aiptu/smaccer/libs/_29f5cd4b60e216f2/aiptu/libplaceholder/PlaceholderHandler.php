<?php

/*
 * Copyright (c) 2024 AIPTU
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/AIPTU/libplaceholder
 */

declare(strict_types=1);

namespace aiptu\smaccer\libs\_29f5cd4b60e216f2\aiptu\libplaceholder;

use pocketmine\player\Player;

interface PlaceholderHandler {
	/**
	 * Handle a placeholder and return its value.
	 *
	 * @param string             $placeholder the name of the placeholder to process
	 * @param PlaceholderContext $context     the context containing relevant data like Player and custom data
	 * @param mixed              ...$args     Additional arguments passed to the placeholder.
	 *
	 * @return string the result of the placeholder processing
	 */
	public function handle(string $placeholder, PlaceholderContext $context, ...$args) : string;
}