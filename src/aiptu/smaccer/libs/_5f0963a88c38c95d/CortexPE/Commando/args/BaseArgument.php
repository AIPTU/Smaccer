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

namespace aiptu\smaccer\libs\_5f0963a88c38c95d\CortexPE\Commando\args;

use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;

abstract class BaseArgument {
	protected string $name;

	protected bool $optional = false;

	protected CommandParameter $parameterData;

	public function __construct(string $name, bool $optional = false) {
		$this->name = $name;

		$this->optional = $optional;

		$this->parameterData = CommandParameter::standard($name, $this->getNetworkType(), 0, $this->isOptional());
	}

	abstract public function getNetworkType() : int;

	abstract public function canParse(string $testString, CommandSender $sender) : bool;

	abstract public function parse(string $argument, CommandSender $sender) : mixed;

	public function getName() : string {
		return $this->name;
	}

	public function isOptional() : bool {
		return $this->optional;
	}

	/**
	 * Returns how much command arguments.
	 *
	 * it takes to build the full argument
	 */
	public function getSpanLength() : int {
		return 1;
	}

	abstract public function getTypeName() : string;

	public function getNetworkParameterData() : CommandParameter {
		return $this->parameterData;
	}
}