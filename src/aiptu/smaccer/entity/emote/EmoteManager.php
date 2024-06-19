<?php

namespace aiptu\smaccer\entity\emote;

class EmoteManager
{
    /** @var EmoteType[] */
    private array $emotes = [];

    public function __construct(array $emotes)
    {
        $this->loadEmotes($emotes);
    }

    /**
     * Load emote from the given array
     *
     * @param array{
     *      array{
     *          uuid: string,
     *          title: string,
     *          image: string
     *      }
     * } $emotes the array of emotes list
     */
    public function loadEmotes(array $emotes)
    {
        // TODO if your want to force load from github using a single command. so its should be empty first
        $this->emotes = [];

        foreach ($emotes as $emote) {
            extract($emote);

            $originalTitle = $title;
            $counter = 2;

            while ($this->ensureUniqueTitle($title)) {
                $title = $originalTitle . " " . $counter;
                $counter++;
            }

            $this->emotes[] = new EmoteType($uuid, $title, $image);
        }
    }

    /**
     * Ensure none of the title are the same
     *
     * @param string $title The title that will be checked
     *
     * @return bool Returns `true` when the title is the same as the one listed and `false` when the title is Unique
     */
    public function ensureUniqueTitle(string $title)
    {
        foreach ($this->emotes as $emote) {
            if ($emote->getTitle() === $title) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get an emote by its uuid
     *
     * @param string $uuid the UUID of the emote
     *
     * @return EmoteType|null returns `EmoteType` class when the uuid exists and `null` if the UUID doesn`t exists
     */
    public function getEmote(string $uuid): ?EmoteType
    {
        foreach ($this->emotes as $emote) {
            if ($emote->getUuid() === $uuid) {
                return $emote;
            }
        }

        return null;
    }

    /**
     * Return all emotes.
     *
     * @return EmoteType[] Returns all of the `EmoteType`
     */
    public function getAll(): array
    {
        return $this->emotes;
    }
}
