<?php

declare(strict_types=1);

namespace aiptu\smaccer\libs\_9723b9e66dd21d7c\frago9876543210\forms\element;

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