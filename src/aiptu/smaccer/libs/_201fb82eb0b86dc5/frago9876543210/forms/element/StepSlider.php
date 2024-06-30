<?php

declare(strict_types=1);

namespace aiptu\smaccer\libs\_201fb82eb0b86dc5\frago9876543210\forms\element;

/** @phpstan-extends BaseSelector<int> */
class StepSlider extends BaseSelector{

	protected function getType() : string{ return "step_slider"; }

	protected function serializeElementData() : array{
		return [
			"steps" => $this->options,
			"default" => $this->default,
		];
	}
}