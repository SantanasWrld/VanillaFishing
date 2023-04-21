<?php

declare(strict_types=1);

namespace santana\fishing\event;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\player\PlayerEvent;
use pocketmine\item\Item;
use pocketmine\player\Player;

final class PlayerFishEvent extends PlayerEvent implements Cancellable
{
    use CancellableTrait;

    /**
     * @var Player
     */
    protected Player $player;

    /**
     * @var Item
     */
    protected Item $fishingRod;

    /**
     * @var Item
     */
    protected Item $loot;

    /**
     * @var int
     */
    protected int $experience;

    /**
     * @param Player $player
     * @param Item $fishingRod
     * @param Item $loot
     * @param int $experience
     */
    public function __construct(Player $player, Item $fishingRod, Item $loot, int $experience)
    {
        $this->player = $player;
        $this->fishingRod = $fishingRod;
        $this->loot = $loot;
        $this->experience = $experience;
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * @param Player $player
     */
    public function setPlayer(Player $player): void
    {
        $this->player = $player;
    }

    /**
     * @return Item
     */
    public function getFishingRod(): Item
    {
        return $this->fishingRod;
    }

    /**
     * @param Item $fishingRod
     */
    public function setFishingRod(Item $fishingRod): void
    {
        $this->fishingRod = $fishingRod;
    }

    /**
     * @return Item
     */
    public function getLoot(): Item
    {
        return $this->loot;
    }

    /**
     * @param Item $loot
     */
    public function setLoot(Item $loot): void
    {
        $this->loot = $loot;
    }

    /**
     * @return int
     */
    public function getExperience(): int
    {
        return $this->experience;
    }

    /**
     * @param int $experience
     */
    public function setExperience(int $experience): void
    {
        $this->experience = $experience;
    }
}