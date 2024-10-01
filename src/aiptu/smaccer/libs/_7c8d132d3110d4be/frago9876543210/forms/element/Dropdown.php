<?php

declare(strict_types=1);

namespace aiptu\smaccer\libs\_7c8d132d3110d4be\frago9876543210\forms\element;

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