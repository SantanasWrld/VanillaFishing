<?php

declare(strict_types=1);

namespace santanaswrld\fishing\event;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\player\PlayerEvent;
use pocketmine\item\Item;
use pocketmine\player\Player;

final class PlayerFishEvent extends PlayerEvent implements Cancellable
{
    use CancellableTrait;

    /**
     * @param Player $player
     * @param Item $fishingRod
     * @param Item $loot
     * @param int $experience
     */
    public function __construct(
        protected Player $player,
        protected Item   $fishingRod,
        protected Item   $loot,
        protected int    $experience
    )
    {
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * @return Item
     */
    public function getFishingRod(): Item
    {
        return $this->fishingRod;
    }

    /**
     * @return Item
     */
    public function getLoot(): Item
    {
        return $this->loot;
    }

    /**
     * @return int
     */
    public function getExperience(): int
    {
        return $this->experience;
    }
}