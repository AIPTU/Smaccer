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
	public const string CREATOR = 'Creator';
	public const string SCALE = 'Scale';
	public const string BABY = 'Baby';
	public const string COMMANDS = 'Commands';
	public const string COMMAND_TYPE_PLAYER = 'player';
	public const string COMMAND_TYPE_SERVER = 'server';
	public const string ROTATE_TO_PLAYERS = 'RotateToPlayers';
	public const string NAMETAG_VISIBLE = 'NametagVisible';
	public const string VISIBILITY = 'Visibility';
	public const string SLAP_BACK = 'SlapBack';
	public const string ACTION_EMOTE = 'ActionEmote';
	public const string EMOTE = 'Emote';
	public const string GRAVITY = 'Gravity';
}
