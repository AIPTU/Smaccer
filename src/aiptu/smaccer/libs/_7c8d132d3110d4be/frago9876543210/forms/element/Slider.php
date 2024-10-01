<?php

declare(strict_types=1);

namespace aiptu\smaccer\libs\_7c8d132d3110d4be\frago9876543210\forms\element;

use pocketmine\form\FormValidationException;
use function gettype;
use function is_float;
use function is_int;

/** @phpstan-extends BaseElement<int|float> */
class Slider extends BaseElement{

	public function __construct(
		string $text,
		public /*readonly*/ float $min,
		public /*readonly*/ float $max,
		public /*readonly*/ float $step = 1.0,
		public /*readonly*/ ?float $default = null,
	){
		parent::__construct($text);

		if($this->min > $this->max){
			throw new \InvalidArgumentException("Slider min value should be less than max value");
		}

		if($default !== null){
			if($default > $this->max || $default < $this->min){
				throw new \InvalidArgumentException("Default must be in range $this->min ... $this->max");
			}
		}else{
			$this->default = $this->min;
		}

		if($step <= 0){
			throw new \InvalidArgumentException("Step must be greater than zero");
		}
	}

	protected function getType() : string{ return "slider"; }

	protected function validateValue(mixed $value) : void{
		if(!is_float($value) && !is_int($value)){
			throw new FormValidationException("Expected float, got " . gettype($value));
		}
		if($value < $this->min || $value > $this->max){
			throw new FormValidationException("Value $value is out of bounds (min $this->min, max $this->max)");
		}
	}

	protected function serializeElementData() : array{
		return [
			"min" => $this->min,
			"max" => $this->max,
			"step" => $this->step,
			"default" => $this->default,
		];
	}
}