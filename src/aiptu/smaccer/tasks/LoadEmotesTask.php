<?php

namespace aiptu\smaccer\tasks;

use aiptu\smaccer\entity\emote\EmoteManager;
use aiptu\smaccer\Smaccer;
use aiptu\smaccer\utils\EmoteUtils;
use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\InternetException;
use RuntimeException;

class LoadEmotesTask extends AsyncTask {

    public function __construct(
        private string $cachedFilePath
    ) {}

    public function onRun() : void {
        $currentCommitId = EmoteUtils::getCurrentCommitId();
        $cachedFile = EmoteUtils::getEmotesFromCache($this->cachedFilePath);

        if($currentCommitId instanceof InternetException) throw new RuntimeException("Failed to fetch current commit ID");

        if(is_null($cachedFile) || $cachedFile["commit_id"] !== $currentCommitId) {
            $emotes = EmoteUtils::getEmotes();
            EmoteUtils::saveEmoteToCache($this->cachedFilePath, $currentCommitId, $emotes);

            if($emotes instanceof InternetException) throw new RuntimeException("Failed to fetch emote list");

            $this->setResult($emotes);
            return;
        }

        $this->setResult($cachedFile["emotes"]);
    }

    public function onCompletion() : void {
        $result = $this->getResult();

        Smaccer::getInstance()->setEmoteManager(new EmoteManager($result));
	}
}