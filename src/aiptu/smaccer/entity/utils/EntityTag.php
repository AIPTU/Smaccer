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

namespace aiptu\smaccer\entity\utils;

class EntityTag {
	public const CREATOR = 'Creator';
	public const SCALE = 'Scale';
	public const BABY = 'Baby';
	public const COMMANDS = 'Commands';
	public const COMMAND_TYPE_PLAYER = 'player';
	public const COMMAND_TYPE_SERVER = 'server';
	public const ROTATE_TO_PLAYERS = 'RotateToPlayers';
	public const NAMETAG_VISIBLE = 'NametagVisible';
	public const VISIBILITY = 'Visibility';
	public const SLAP_BACK = 'SlapBack';
	public const ACTION_EMOTE = 'ActionEmote';
	public const EMOTE = 'Emote';
	public const GRAVITY = 'Gravity';
}
