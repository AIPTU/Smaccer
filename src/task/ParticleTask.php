<?php

declare(strict_types=1);

namespace aiptu\smaccer\task;

use aiptu\smaccer\entity\HumanSmaccer;
use aiptu\smaccer\Smaccer;
use pocketmine\color\Color;
use pocketmine\scheduler\Task;
use pocketmine\world\particle\DustParticle;
use function cos;
use function deg2rad;
use function mt_rand;
use function sin;

class ParticleTask extends Task {
	private int $tick = 0;
	private const RADIUS = 0.8;
	private const ANGLE_INCREMENT = 0.09;

	public function onRun() : void {
		$server = Smaccer::getInstance()->getServer();
		foreach ($server->getWorldManager()->getWorlds() as $world) {
			foreach ($world->getEntities() as $entity) {
				if ($entity instanceof HumanSmaccer) {
					$entityPos = $entity->getPosition();
					$entityWorld = $entityPos->getWorld();
					$entityScale = $entity->getScale();

					$angle = $this->tick / self::ANGLE_INCREMENT;
					$offsetX = cos(deg2rad($angle)) * self::RADIUS;
					$offsetZ = sin(deg2rad($angle)) * self::RADIUS;
					$offsetY = 2 * $entityScale;

					$particle1Pos = $entityPos->add(-$offsetX, $offsetY, -$offsetZ);
					$particle2Pos = $entityPos->add(-$offsetZ, $offsetY, -$offsetX);

					$particle1Color = new Color(mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
					$particle2Color = new Color(mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));

					$particle1 = new DustParticle($particle1Color);
					$particle2 = new DustParticle($particle2Color);

					$entityWorld->addParticle($particle1Pos, $particle1);
					$entityWorld->addParticle($particle2Pos, $particle2);
					++$this->tick;
				}
			}
		}
	}
}
