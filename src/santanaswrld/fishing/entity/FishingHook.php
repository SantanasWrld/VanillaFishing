<?php

declare(strict_types=1);

namespace santanaswrld\fishing\entity;

use pocketmine\block\Air;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\EntityCombustByEntityEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\types\ActorEvent;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;
use pocketmine\utils\Random;
use pocketmine\world\particle\BubbleParticle;
use pocketmine\world\particle\WaterParticle;
use santanaswrld\fishing\common\Utility;
use santanaswrld\fishing\event\PlayerFishEvent;
use santanaswrld\fishing\FishingPlugin;
use santanaswrld\fishing\item\FishingRod;

final class FishingHook extends Projectile
{
    /**
     * @var float
     */
    public float $width = 0.15;

    /**
     * @var float
     */
    public float $height = 0.15;

    /**
     * @var int
     */
    public int $waitingTimer = 120;

    /**
     * @var bool
     */
    public bool $attracted = false;

    /**
     * @var bool
     */
    public bool $caught = false;

    /**
     * @var int
     */
    public int $caughtTimer = 0;

    /**
     * @var Vector3|null
     */
    public ?Vector3 $fish = null;

    /**
     * @return string
     */
    public static function getNetworkTypeId(): string
    {
        return EntityIds::FISHING_HOOK;
    }

    /**
     * @return int
     */
    public function getWaitingTimer(): int
    {
        return $this->waitingTimer;
    }

    /**
     * @return bool
     */
    public function isAttracted(): bool
    {
        return $this->attracted;
    }

    /**
     * @return bool
     */
    public function isCaught(): bool
    {
        return $this->caught;
    }

    /**
     * @return int
     */
    public function getCaughtTimer(): int
    {
        return $this->caughtTimer;
    }

    /**
     * @return Vector3|null
     */
    public function getFish(): ?Vector3
    {
        return $this->fish;
    }

    /**
     * @return float
     */
    public function getGravity(): float
    {
        return $this->gravity;
    }

    /**
     * @param float $gravity
     * @return void
     */
    public function setGravity(float $gravity): void
    {
        $this->gravity = $gravity;
    }

    /**
     * @return float
     */
    public function getBaseDamage(): float
    {
        return 0;
    }

    /**
     * @return float
     */
    public function getDamage(): float
    {
        return 0;
    }

    /**
     * @param Entity $entityHit
     * @param RayTraceResult $hitResult
     * @return void
     */
    protected function onHitEntity(Entity $entityHit, RayTraceResult $hitResult): void
    {
        if ($this->getTargetEntity() === null) {
            $damage = $this->getResultDamage();
            if ($damage >= 0) {
                if ($this->getOwningEntity() === null) {
                    $event = new EntityDamageByEntityEvent($this, $entityHit, EntityDamageEvent::CAUSE_PROJECTILE, $damage);
                } else {
                    $event = new EntityDamageByChildEntityEvent($this->getOwningEntity(), $this, $entityHit, EntityDamageEvent::CAUSE_PROJECTILE, $damage);
                }
                $entityHit->attack($event);
                if (!$event->isCancelled()) {
                    $this->setTargetEntity($entityHit);
                    if ($this->isOnFire()) {
                        $event = new EntityCombustByEntityEvent($this, $entityHit, mt_rand(3, 5));
                        $event->call();
                        if (!$event->isCancelled()) {
                            $entityHit->setOnFire($event->getDuration());
                        }
                    }
                }
            }
        }
    }

    /**
     * @return bool
     */
    public function canCollide(): bool
    {
        return $this->getTargetEntity() === null;
    }

    /**
     * @param int $currentTick
     * @return bool
     */
    public function onUpdate(int $currentTick): bool
    {
        $target = $this->getTargetEntity();
        if ($target !== null) {
            if (!$target->isAlive()) {
                $this->setTargetEntity(null);
            } else {
                $this->setPositionAndRotation($target->getPosition()->add(0, $target->getEyeHeight(), 0), 0, 0);
                $this->setForceMovementUpdate();
            }
        }
        if ($this->getOwningEntity() == null) {
            $this->flagForDespawn();
        }
        $player = $this->getOwningEntity();
        if ($player instanceof Player) {
            $item = $player->getInventory()->getItemInHand();
            if (!($item instanceof FishingRod)) {
                $this->flagForDespawn();
            }
        }
        if ($this->getPosition()->distance($player->getPosition()->asVector3()) >= 32) {
            $this->flagForDespawn();
        }
        if (!($hasUpdate = parent::onUpdate($currentTick))) {
            return false;
        }
        if ($this->isUnderwater()) {
            $this->motion->x = 0;
            $this->motion->y = 0.16;
            $this->motion->z = 0;
            $hasUpdate = true;
        } else if ($this->isCollided and $this->keepMovement) {
            $this->motion->x = 0;
            $this->motion->z = 0;
            $this->keepMovement = false;
            $hasUpdate = true;
        }
        $random = new Random();
        if ($this->isUnderwater()) {
            if (!$this->attracted) {
                if ($this->waitingTimer > 0) {
                    $this->waitingTimer--;
                }
                if ($this->waitingTimer == 0) {
                    $this->spawnFish();
                    $this->caught = false;
                    $this->attracted = true;
                }
            } else if (!$this->caught) {
                if ($this->attractFish()) {
                    $this->caughtTimer = $random->nextBoundedInt(20) + 40;
                    $this->caughtTimer = $random->nextBoundedInt(20) + 20;
                    $this->fishBites();
                    $this->caught = true;
                }
            } else {
                if ($this->caughtTimer > 0) {
                    $this->caughtTimer--;
                }
                if ($this->caughtTimer == 0) {
                    $this->attracted = false;
                    $this->caught = false;
                    $this->waitingTimer = mt_rand(100, 600); //TODO: Customize this
                }
            }
        }
        return $hasUpdate; // always returns true?
    }

    /**
     * @return int
     */
    public function getWaterHeight(): int
    {
        for ($y = $this->getPosition()->getFloorY(); $y < 256; $y++) {
            $block = $this->getWorld()->getBlockAt($this->getPosition()->getFloorX(), $y, $this->getPosition()->getFloorZ());
            if ($block instanceof Air) {
                return $y;
            }
        }
        return $this->getPosition()->getFloorY();
    }

    /**
     * @return void
     */
    public function spawnFish(): void
    {
        $random = new Random();
        $position = $this->getPosition();
        $this->fish = new Vector3(
            x: $position->x + ($random->nextFloat() * 1.2 + mt_rand(1, 4)) * ($random->nextFloat() ? -1 : 1),
            y: $this->getWaterHeight(),
            z: $position->z + ($random->nextFloat() * 1.2 + mt_rand(1, 4)) * ($random->nextFloat() ? -1 : 1)
        );
    }

    /**
     * @return void
     */
    public function reelLine(): void
    {
        if ($this->getOwningEntity() instanceof Player) {
            /** @var Player $player */
            $player = $this->getOwningEntity();
            if ($this->caught) {
                $motion = $player->getPosition()->subtractVector($this->getPosition())->multiply(0.1);
                $motion->y += sqrt($player->getPosition()->distance($this->getPosition())) * 0.08;
                $event = new PlayerFishEvent($player, $player->getInventory()->getItemInHand(), Utility::getRandomLoot(), mt_rand(1, 3));
                $event->call();
                if (!$event->isCancelled()) {
                    $itemEntity = $player->getWorld()->dropItem($this->getPosition(), $event->getLoot(), $motion);
                    if ($itemEntity !== null) {
                        $itemEntity->spawnToAll();
                        $player->getXpManager()->addXp($event->getExperience());
                    }
                }
            }
            if ($this->getTargetEntity() !== null) {
                $motion = $player->getDirectionVector()->multiply(-1);
                $this->getTargetEntity()->setMotion($motion);
            }
        }
        $this->flagForDespawn();
    }

    /**
     * @return bool
     */
    public function attractFish(): bool
    {
        $multiply = 0.1;
        $position = $this->getPosition();
        $this->fish = $this->fish->withComponents(
            x: $this->fish->x + ($position->x - $this->fish->x) * $multiply,
            y: $this->fish->y,
            z: $this->fish->z + ($position->z - $this->fish->z) * $multiply
        );
        $this->getWorld()->addParticle($this->fish, new WaterParticle());
        $dist =
            abs(sqrt($position->x * $position->x + $position->z * $position->z) -
            sqrt($this->fish->x * $this->fish->x + $this->fish->z * $this->fish->z));
        if ($dist < 0.15) {
            return true;
        }
        return false;
    }

    /**
     * @return void
     */
    public function fishBites(): void
    {
        $packet1 = new ActorEventPacket();
        $packet1->actorRuntimeId = $this->getId();
        $packet1->eventId = ActorEvent::FISH_HOOK_HOOK;
        $packet2 = new ActorEventPacket();
        $packet2->actorRuntimeId = $this->getId();
        $packet2->eventId = ActorEvent::FISH_HOOK_BUBBLE;
        $packet3 = new ActorEventPacket();
        $packet3->actorRuntimeId = $this->getId();
        $packet3->eventId = ActorEvent::FISH_HOOK_TEASE;
        foreach ($this->getViewers() as $player) {
            $player->getNetworkSession()->sendDataPacket($packet1);
            $player->getNetworkSession()->sendDataPacket($packet2);
            $player->getNetworkSession()->sendDataPacket($packet3);
        }
        $random = new Random();
        $position = $this->getPosition();
        for ($i = 0; $i < 5; $i++) {
            $this->getWorld()->addParticle($position->withComponents($position->x + $random->nextFloat() * 0.5 - 0.25, $this->getWaterHeight(), $position->z + $random->nextFloat() * 0.5 - 0.25), new BubbleParticle());
        }
        $this->motion->y -= 1;
    }

    /**
     * @return float
     */
    public function getWidth(): float
    {
        return $this->width;
    }

    /**
     * @return float
     */
    public function getHeight(): float
    {
        return $this->height;
    }

    /**
     * @return EntitySizeInfo
     */
    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo($this->height, $this->width);
    }

    /**
     * @param CompoundTag $nbt
     * @return void
     */
    protected function initEntity(CompoundTag $nbt): void
    {
        parent::initEntity($nbt);
        $this->setCanSaveWithChunk(false);
        $this->waitingTimer = mt_rand(100, 600); // TODO: Customize this
    }

    /**
     * @return float
     */
    protected function getInitialDragMultiplier(): float
    {
        return 0.02;
    }

    /**
     * @return float
     */
    protected function getInitialGravity(): float
    {
        return 0.05;
    }
}