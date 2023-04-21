<?php

declare(strict_types=1);

namespace santana\fishing\item;

use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\item\Durable;
use pocketmine\item\ItemUseResult;
use pocketmine\item\Releasable;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use santana\fishing\entity\FishingHook;
use pocketmine\world\sound\ThrowSound;

final class FishingRod extends Durable implements Releasable
{
    /**
     * @var array
     */
    protected static array $hooked = [];

    /**
     * @param Player $player
     * @return FishingHook|null
     */
    public static function getHooked(Player $player): ?FishingHook
    {
        return self::$hooked[$player->getId()] ?? null;
    }

    /**
     * @param Player $player
     * @param FishingHook|null $hook
     * @return void
     */
    public static function setHooked(Player $player, ?FishingHook $hook): void
    {
        self::$hooked[$player->getId()] = $hook;
    }

    /**
     * @return int
     */
    public function getMaxDurability(): int
    {
        return 384;
    }

    /**
     * @return int
     */
    public function getMaxStackSize(): int
    {
        return 1;
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function canStartUsingItem(Player $player): bool
    {
        return true;
    }

    /**
     * @param Player $player
     * @param Vector3 $directionVector
     * @return ItemUseResult
     */
    public function onClickAir(Player $player, Vector3 $directionVector): ItemUseResult
    {
        $hook = self::getHooked($player);
        if ($hook !== null and $hook->isFlaggedForDespawn() == false) {
            $changed = false;
            if ($hook->caught) {
                $changed = true;
                $this->applyDamage(1);
            }
            if ($hook->getTargetEntity() !== null) {
                $changed = true;
                $this->applyDamage(mt_rand(1, 2));
            }
            $hook->reelLine();
            self::setHooked($player, null);
            return $changed ? ItemUseResult::SUCCESS() : ItemUseResult::NONE();
        } else {
            $location = $player->getLocation();
            $location->y += $player->getEyeHeight();
            $hook = new FishingHook($location, $player, null);
            $hook->setMotion($player->getDirectionVector()->multiply(0.7));
            $event = new ProjectileLaunchEvent($hook);
            $event->call();
            if ($event->isCancelled()) {
                $hook->close();
            } else {
                $hook->spawnToAll();
                $location->getWorld()->addSound($location, new ThrowSound(), [$player]);
                self::setHooked($player, $hook);
            }
        }
        return ItemUseResult::NONE();
    }
}
