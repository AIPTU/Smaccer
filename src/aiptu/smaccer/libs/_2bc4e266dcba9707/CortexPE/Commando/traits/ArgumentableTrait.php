<?php

/*
 *    ___                                          _
 *   / __\___  _ __ ___  _ __ ___   __ _ _ __   __| | ___
 *  / /  / _ \| '_ ` _ \| '_ ` _ \ / _` | '_ \ / _` |/ _ \
 * / /__| (_) | | | | | | | | | | | (_| | | | | (_| | (_) |
 * \____/\___/|_| |_| |_|_| |_| |_|\__,_|_| |_|\__,_|\___/
 *
 * Commando - A Command Framework virion for PocketMine-MP
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * Written by @CortexPE <https://CortexPE.xyz>
 *
 */
declare(strict_types=1);

namespace aiptu\smaccer\libs\_2bc4e266dcba9707\CortexPE\Commando\traits;

use aiptu\smaccer\libs\_2bc4e266dcba9707\CortexPE\Commando\args\BaseArgument;
use aiptu\smaccer\libs\_2bc4e266dcba9707\CortexPE\Commando\args\TextArgument;
use aiptu\smaccer\libs\_2bc4e266dcba9707\CortexPE\Commando\BaseCommand;
use aiptu\smaccer\libs\_2bc4e266dcba9707\CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use function array_slice;
use function count;
use function implode;
use function is_array;
use function rtrim;
use function trim;
use function usort;
use const PHP_INT_MAX;

trait ArgumentableTrait {
	/** @var array<int, array<int, BaseArgument>> */
	private array $argumentList = [];

	/** @var array<int, bool> */
	private array $requiredArgumentCount = [];

	abstract protected function prepare() : void;

	/**
	 * @throws ArgumentOrderException
	 */
	public function registerArgument(int $position, BaseArgument $argument) : void {
		if ($position < 0) {
			throw new ArgumentOrderException('You cannot register arguments at negative positions');
		}

		if ($position > 0 && !isset($this->argumentList[$position - 1])) {
			throw new ArgumentOrderException("There were no arguments before {$position}");
		}

		$previousArgs = $this->argumentList[$position - 1] ?? [];
		if (count($previousArgs) > 0) {
			foreach ($previousArgs as $arg) {
				if ($arg instanceof TextArgument) {
					throw new ArgumentOrderException('No other arguments can be registered after a TextArgument');
				}

				if ($arg->isOptional() && !$argument->isOptional()) {
					throw new ArgumentOrderException('You cannot register a required argument after an optional argument');
				}
			}
		}

		$this->argumentList[$position][] = $argument;
		if (!$argument->isOptional()) {
			$this->requiredArgumentCount[$position] = true;
		}
	}

	public function parseArguments(array $rawArgs, CommandSender $sender) : array {
		$return = [
			'arguments' => [],
			'errors' => [],
		];

		$required = count($this->requiredArgumentCount);
		$rawArgsCount = count($rawArgs);

		// Check if command doesn't take args but sender gives args anyway
		if (count($this->argumentList) === 0 && $rawArgsCount > 0) {
			$return['errors'][] = [
				'code' => BaseCommand::ERR_NO_ARGUMENTS,
				'data' => [],
			];
		}

		$offset = 0;
		$argOffset = 0;

		if ($rawArgsCount > 0) {
			foreach ($this->argumentList as $pos => $possibleArguments) {
				// Sort: unlimited span arguments go last
				usort(
					$possibleArguments,
					static fn (BaseArgument $a) : int => $a->getSpanLength() === PHP_INT_MAX ? 1 : -1
				);

				$parsed = false;
				$optional = true;
				$arg = '';

				foreach ($possibleArguments as $argument) {
					$len = $argument->getSpanLength();
					$arg = trim(implode(' ', array_slice($rawArgs, $offset, $len)));

					if (!$argument->isOptional()) {
						$optional = false;
					}

					if ($arg !== '' && $argument->canParse($arg, $sender)) {
						$k = $argument->getName();
						$result = (clone $argument)->parse($arg, $sender);

						if (isset($return['arguments'][$k])) {
							if (!is_array($return['arguments'][$k])) {
								$return['arguments'][$k] = [$return['arguments'][$k]];
							}

							$return['arguments'][$k][] = $result;
						} else {
							$return['arguments'][$k] = $result;
						}

						if (!$optional) {
							--$required;
						}

						$offset += $len;
						++$argOffset;
						$parsed = true;
						break;
					}

					if ($offset > $rawArgsCount) {
						break;
					}
				}

				// Check if parsing failed and argument is not optional or empty
				if (!$parsed && !($optional && $arg === '')) {
					$expectedArgs = $this->argumentList[$argOffset];
					$expected = '';

					foreach ($expectedArgs as $expectedArg) {
						$expected .= $expectedArg->getTypeName() . '|';
					}

					$return['errors'][] = [
						'code' => BaseCommand::ERR_INVALID_ARG_VALUE,
						'data' => [
							'value' => $rawArgs[$offset] ?? '',
							'position' => $pos + 1,
							'expected' => rtrim($expected, '|'),
						],
					];

					return $return;
				}
			}
		}

		// Check for too many arguments
		if ($offset < $rawArgsCount) {
			$return['errors'][] = [
				'code' => BaseCommand::ERR_TOO_MANY_ARGUMENTS,
				'data' => [],
			];
		}

		// Check for insufficient required arguments
		if ($required > 0) {
			$return['errors'][] = [
				'code' => BaseCommand::ERR_INSUFFICIENT_ARGUMENTS,
				'data' => [],
			];
		}

		// Handle combined error case
		$errorCount = count($return['errors']);
		if (
			$errorCount === 2
			&& $return['errors'][0]['code'] === BaseCommand::ERR_NO_ARGUMENTS
			&& $return['errors'][1]['code'] === BaseCommand::ERR_TOO_MANY_ARGUMENTS
		) {
			$return['errors'] = [[
				'code' => BaseCommand::ERR_INVALID_ARGUMENTS,
				'data' => [],
			]];
		}

		return $return;
	}

	public function generateUsageMessage(string $parent = '') : string {
		$name = $parent . ($parent === '' ? '' : ' ') . $this->getName();
		$msg = TextFormat::RED . '/' . $name;
		$args = [];

		foreach ($this->argumentList as $arguments) {
			$hasOptional = false;
			$names = [];

			foreach ($arguments as $argument) {
				$names[] = $argument->getName() . ':' . $argument->getTypeName();
				if ($argument->isOptional()) {
					$hasOptional = true;
				}
			}

			$names = implode('|', $names);
			$args[] = $hasOptional ? '[' . $names . ']' : '<' . $names . '>';
		}

		$argsStr = count($args) > 0 ? ' ' . implode(TextFormat::RED . ' ', $args) : '';
		$msg .= $argsStr . ': ' . $this->getDescription();

		foreach ($this->subCommands as $label => $subCommand) {
			if ($label === $subCommand->getName()) {
				$msg .= "\n - " . $subCommand->generateUsageMessage($name);
			}
		}

		return trim($msg);
	}

	public function hasArguments() : bool {
		return count($this->argumentList) > 0;
	}

	public function hasRequiredArguments() : bool {
		foreach ($this->argumentList as $arguments) {
			foreach ($arguments as $argument) {
				if (!$argument->isOptional()) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @return array<int, array<int, BaseArgument>>
	 */
	public function getArgumentList() : array {
		return $this->argumentList;
	}
}