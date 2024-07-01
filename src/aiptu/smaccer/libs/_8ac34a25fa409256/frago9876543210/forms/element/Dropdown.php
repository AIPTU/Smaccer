<?php

declare(strict_types=1);

namespace aiptu\smaccer\libs\_8ac34a25fa409256\frago9876543210\forms\element;

/** @phpstan-extends BaseSelector<int> */
class Dropdown extends BaseSelector{

	protected function getType() : string{ return "dropdown"; }

	protected function serializeElementData() : array{
		return [
			"options" => $this->options,
			"default" => $this->default,
		];
	}
}