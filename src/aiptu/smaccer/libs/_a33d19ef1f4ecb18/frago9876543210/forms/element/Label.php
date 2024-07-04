<?php

declare(strict_types=1);

namespace aiptu\smaccer\libs\_a33d19ef1f4ecb18\frago9876543210\forms\element;

use pocketmine\form\FormValidationException;
use function gettype;
use function is_null;

/** @phpstan-extends BaseElement<null> */
class Label extends BaseElement{

	public function __construct(string $text){
		parent::__construct($text);
	}

	protected function getType() : string{ return "label"; }

	protected function validateValue(mixed $value) : void{
		if(!is_null($value)){
			throw new FormValidationException("Expected null, got " . gettype($value));
		}
	}

	protected function serializeElementData() : array{
		return [];
	}
}