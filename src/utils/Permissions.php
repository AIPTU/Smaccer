<?php

/*
 * Copyright (c) 2024 AIPTU
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/AIPTU/Smaccer
 */

declare(strict_types=1);

namespace aiptu\smaccer\utils;

class Permissions {
	public const BYPASS_COOLDOWN = 'smaccer.bypass.cooldown';
	public const COMMAND_CREATE_SELF = 'smaccer.command.create.self';
	public const COMMAND_CREATE_OTHERS = 'smaccer.command.create.others';
	public const COMMAND_DELETE_SELF = 'smaccer.command.delete.self';
	public const COMMAND_DELETE_OTHERS = 'smaccer.command.delete.others';
	public const COMMAND_ID = 'smaccer.command.id';
	public const COMMAND_LIST = 'smaccer.command.list';
	public const COMMAND_MOVE_SELF = 'smaccer.command.move.self';
	public const COMMAND_MOVE_OTHERS = 'smaccer.command.move.others';
	public const COMMAND_TELEPORT_SELF = 'smaccer.command.teleport.self';
	public const COMMAND_TELEPORT_OTHERS = 'smaccer.command.teleport.others';
}
