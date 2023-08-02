<?php

/**
 * `7MM"""Mq.                 `7MM              mm        db     `7MMF'
 *   MM   `MM.                  MM              MM       ;MM:      MM
 *   MM   ,M9 ,pW"Wq.   ,p6"bo  MM  ,MP.gP"Ya mmMMmm    ,V^MM.     MM
 *   MMmmdM9 6W'   `Wb 6M'  OO  MM ;Y ,M'   Yb  MM     ,M  `MM     MM
 *   MM      8M     M8 8M       MM;Mm 8M""""""  MM     AbmmmqMA    MM
 *   MM      YA.   ,A9 YM.    , MM `MbYM.    ,  MM    A'     VML   MM
 * .JMML.     `Ybmd9'   YMbmd'.JMML. YA`Mbmmd'  `Mbm.AMA.   .AMMA.JMML.
 *
 * This file was generated using PocketAI, Branch V7.13.1+dev
 *
 * PocketAI is private software: You can redistribute the files under
 * the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 *
 * This plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this file. If not, see <http://www.gnu.org/licenses/>.
 *
 * @ai-profile SantanasWrld
 * @copyright 2023
 * @authors NopeNotDark, SantanasWrld
 * @link https://thedarkproject.net/pocketai
 */

declare(strict_types=1);

namespace santanaswrld\fishing;

use pocketmine\data\bedrock\item\ItemTypeNames;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\StringToItemParser;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use ReflectionClass;
use ReflectionException;
use santanaswrld\fishing\common\Utility;
use santanaswrld\fishing\item\FishingRod;
use function Ramsey\Uuid\v4;

final class FishingPlugin extends PluginBase
{
    use SingletonTrait {
        getInstance as protected getSingletonInstance;
        setInstance as protected;
    }

    /**
     * @return FishingPlugin
     */
    public static function getInstance(): FishingPlugin
    {
        return FishingPlugin::getSingletonInstance();
    }

    /**
     * @return void
     */
    protected function onLoad(): void
    {
        foreach ($this->getResources() as $resource) {
            $this->saveResource($resource->getFilename());
        }
        FishingPlugin::setInstance($this);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    protected function onEnable(): void
    {
        $itemDeserializer = GlobalItemDataHandlers::getDeserializer();
        $fishingRod = new FishingRod(new ItemIdentifier(ItemTypeIds::FISHING_ROD), "Fishing Rod");
        $itemDeserializer->map('minecraft:fishing_rod', fn() => clone $fishingRod);
        CreativeInventory::getInstance()->add($fishingRod);
        StringToItemParser::getInstance()->override("fishing_rod", fn() => $fishingRod);
        $reflectionDeserializer = new ReflectionClass($itemDeserializer);
        $reflectionProperty3 = $reflectionDeserializer->getProperty("deserializers");
        $reflectionProperty3->setAccessible(true);
        $itemDeserializer->map('minecraft:fishing_rod', function (SavedItemData $data) use ($fishingRod) {
            $newFishingRod = new FishingRod(new ItemIdentifier(ItemTypeIds::FISHING_ROD), "Fishing Rod");
            return clone $fishingRod;
        });
        Utility::ensureFile();
        $itemSerializer = GlobalItemDataHandlers::getSerializer();
        $reflectionSerializer = new ReflectionClass($itemSerializer);
        $reflectionProperty = $reflectionSerializer->getProperty("itemSerializers");
        $reflectionProperty->setAccessible(true);
        $val = $reflectionProperty->getValue($itemSerializer);
        unset($val[$fishingRod->getTypeId()]);
        $val[$fishingRod->getTypeId()][get_class($fishingRod)] = fn() => new SavedItemData(ItemTypeNames::FISHING_ROD);
        $reflectionProperty->setValue($itemSerializer, $val);

        $reflectionHandler = new ReflectionClass(GlobalItemDataHandlers::class);
        $reflectionProperty2 = $reflectionHandler->getProperty("itemSerializer");
        $reflectionProperty2->setAccessible(true);
        $reflectionProperty2->setValue(GlobalItemDataHandlers::class, $itemSerializer);
    }

    /**
     * @param Item $item
     * @param float $chance
     * @return void
     */
    protected function registerTableReward(Item $item, float $chance): void
    {
        Utility::getLootTable()[(v4())] = [
            "item" => $item,
            "chance" => $chance
        ];
    }
}
