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

namespace aiptu\smaccer\entity\trait;

use aiptu\smaccer\event\NPCNameTagChangeEvent;
use function str_replace;

trait NametagTrait {
	public function setNameTag(string $nameTag) : void {
		$oldNameTag = $this->getNameTag();
		$event = new NPCNameTagChangeEvent($this, $oldNameTag, $this->replaceOldPlaceholders($nameTag));
		$event->call();

		if (!$event->isCancelled()) {
			parent::setNameTag($event->getNewNameTag());
		}
	}

	/**
	 * Replaces old placeholders with the updated format.
	 */
	private function replaceOldPlaceholders(string $text) : string {
		return str_replace(
			['{player}', '{display_name}'],
			['{player:name}', '{player:display_name}'],
			$text
		);
	}
}
