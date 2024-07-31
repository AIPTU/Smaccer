<?php

declare(strict_types=1);

namespace aiptu\smaccer\libs\_8775a6c101bbcee0\frago9876543210\forms\element;

use pocketmine\form\FormValidationException;
use function gettype;
use function is_bool;

/** @phpstan-extends BaseElement<bool> */
class Toggle extends BaseElement{

	public function __construct(
		string $text,
		public /*readonly*/ bool $default = false,
	){
		parent::__construct($text);
	}

	public function hasChanged() : bool{
		return $this->default !== $this->getValue();
	}

	protected function getType() : string{ return "toggle"; }

	protected function validateValue(mixed $value) : void{
		if(!is_bool($value)){
			throw new FormValidationException("Expected bool, got " . gettype($value));
		}
	}

	protected function serializeElementData() : array{
		return [
			"default" => $this->default,
		];
	}
}