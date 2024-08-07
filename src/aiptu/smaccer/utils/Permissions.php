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
	public const string BYPASS_COOLDOWN = 'smaccer.bypass.cooldown';
	public const string COMMAND_ABOUT = 'smaccer.command.about';
	public const string COMMAND_CREATE_SELF = 'smaccer.command.create.self';
	public const string COMMAND_CREATE_OTHERS = 'smaccer.command.create.others';
	public const string COMMAND_DELETE_SELF = 'smaccer.command.delete.self';
	public const string COMMAND_DELETE_OTHERS = 'smaccer.command.delete.others';
	public const string COMMAND_EDIT_SELF = 'smaccer.command.edit.self';
	public const string COMMAND_EDIT_OTHERS = 'smaccer.command.edit.others';
	public const string COMMAND_ID = 'smaccer.command.id';
	public const string COMMAND_LIST = 'smaccer.command.list';
	public const string COMMAND_MOVE_SELF = 'smaccer.command.move.self';
	public const string COMMAND_MOVE_OTHERS = 'smaccer.command.move.others';
	public const string COMMAND_RELOAD_CONFIG = 'smaccer.command.reload.config';
	public const string COMMAND_RELOAD_EMOTES = 'smaccer.command.reload.emotes';
	public const string COMMAND_TELEPORT_SELF = 'smaccer.command.teleport.self';
	public const string COMMAND_TELEPORT_OTHERS = 'smaccer.command.teleport.others';
}
