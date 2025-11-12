<?php

/*
 * Copyright (c) 2024-2025 AIPTU
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/AIPTU/Smaccer
 */

declare(strict_types=1);

namespace aiptu\smaccer\tasks;

use aiptu\smaccer\entity\query\QueryInfo;
use aiptu\smaccer\libs\_2bc4e266dcba9707\jasonw4331\libpmquery\PMQuery;
use aiptu\smaccer\libs\_2bc4e266dcba9707\jasonw4331\libpmquery\PmQueryException;
use pmmp\thread\ThreadSafeArray;
use pocketmine\scheduler\AsyncTask;
use function is_array;
use function str_replace;

/**
 * @phpstan-type TaskData array{
 *     ip: string,
 *     port: int,
 *     messages: array{
 *         online: string,
 *         offline: string
 *     },
 *     cacheKey: string
 * }
 */
class QueryServerTask extends AsyncTask {
	/** @var ThreadSafeArray<int, TaskData> */
	private ThreadSafeArray $taskData;

	/**
	 * @param array<int, TaskData> $taskData
	 */
	public function __construct(array $taskData) {
		$this->taskData = ThreadSafeArray::fromArray($taskData);
	}

	public function onRun() : void {
		$resultData = [];
		foreach ($this->taskData as $data) {
			/** @var TaskData $data */
			try {
				$queryData = PMQuery::query($data['ip'], $data['port']);
				$onlinePlayers = $queryData['Players'];
				$maxOnlinePlayers = $queryData['MaxPlayers'];
				$resultMessage = str_replace(
					['{online}', '{max_online}'],
					[$onlinePlayers, $maxOnlinePlayers],
					$data['messages']['online']
				);
			} catch (PmQueryException $e) {
				$resultMessage = $data['messages']['offline'];
			}

			$resultData[] = [
				'cacheKey' => $data['cacheKey'],
				'resultMessage' => $resultMessage,
			];
		}

		$this->setResult($resultData);
	}

	public function onCompletion() : void {
		$result = $this->getResult();
		if (is_array($result)) {
			foreach ($result as $data) {
				/** @var array{cacheKey: string, resultMessage: string} $data */
				QueryInfo::updateCache($data['cacheKey'], $data['resultMessage']);
			}
		}
	}
}