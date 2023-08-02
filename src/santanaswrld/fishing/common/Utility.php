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

namespace santanaswrld\fishing\common;

use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\utils\Filesystem;
use santanaswrld\fishing\FishingPlugin;

final class Utility
{
    /**
     * @var array
     */
    protected static array $lootTable = [];

    /**
     * @param float $chance
     * @return bool
     */
    public static function chance(float $chance): bool
    {
        $string = strrchr(strval($chance), ".");
        if (!$string) return mt_rand(1, 100) <= $chance;
        $count = strlen(substr($string, 1));
        $multiply = intval("1" . str_repeat("0", $count));
        return mt_rand(1, (100 * $multiply)) <= ($chance * $multiply);
    }

    /**
     * @return void
     * Values Taken from https://minecraft.fandom.com/wiki/Fishing
     */
    protected function registerVanillaLoot(): void
    {
        // TODO: Add a System for Making Randomly Enchanted Items
        $this->addItemToLootTable(VanillaItems::RAW_FISH(), 60.0);
        $this->addItemToLootTable(VanillaItems::RAW_SALMON(), 25.0);
        $this->addItemToLootTable(VanillaItems::CLOWNFISH(), 2.0);
        $this->addItemToLootTable(VanillaItems::PUFFERFISH(), 13.0);
        $this->addItemToLootTable(VanillaItems::BOW(), 16.7);
//      $this->addItemToLootTable($this->getRandomlyEnchantedItem(ItemIds::BOW), 0.8);
//      $this->addItemToLootTable($this->getRandomlyEnchantedItem(ItemIds::BOOK), 0.8);
        $this->addItemToLootTable(VanillaItems::FISHING_ROD(), 16.7);
//      $this->addItemToLootTable($this->getRandomlyEnchantedItem(ItemIds::FISHING_ROD), 0.8);
//        $this->addItemToLootTable(ItemFactory::getInstance()->get(ItemIds::NAMETAG), 16.7); //TODO: add this when nametags was added to pm5
        $this->addItemToLootTable(VanillaItems::NAUTILUS_SHELL(), 16.7);
//        $this->addItemToLootTable(ItemFactory::getInstance()->get(ItemIds::SADDLE), 16.7); //TODO: add this when saddles was added to pm5
//        $this->addItemToLootTable(ItemFactory::getInstance()->get(ItemIds::LILY_PAD), 17.0); //TODO: add this when lily pads was added to pm5
        $this->addItemToLootTable(VanillaItems::BOWL(), 10.0);
        $this->addItemToLootTable(VanillaItems::FISHING_ROD(), 2.0); // Add a Randomly Enchanted Bow instead
        $this->addItemToLootTable(VanillaItems::LEATHER(), 10.0);
        $this->addItemToLootTable(VanillaItems::LEATHER_BOOTS(), 10.0);
        $this->addItemToLootTable(VanillaItems::ROTTEN_FLESH(), 10.0);
        $this->addItemToLootTable(VanillaItems::STICK(), 5.0);
        $this->addItemToLootTable(VanillaItems::STRING(), 5.0);
        $this->addItemToLootTable(VanillaItems::GLASS_BOTTLE(), 10.0);
        $this->addItemToLootTable(VanillaItems::BONE(), 10.0);
        $this->addItemToLootTable(VanillaItems::DYE(), 1.0);
//        $this->addItemToLootTable(ItemFactory::getInstance()->get(ItemIds::TRIPWIRE_HOOK), 10.0); //TODO: add this when tripwire hooks was added to pm5
    }

    /**
     * @return array
     */
    public static function &getLootTable(): array
    {
        return self::$lootTable;
    }

    /**
     * @return Item
     */
    public static function getRandomLoot(): Item
    {
        foreach (self::$lootTable as $data) {
            if (Utility::chance($data["chance"])) {
                return clone $data["item"];
            }
        }
        return VanillaItems::AIR();
    }

    /**
     * @return void
     */
    public static function ensureFile(): void
    {
        if (!file_exists(($path = FishingPlugin::getInstance()->getDataFolder() . "lootTable.json")))
            Filesystem::safeFilePutContents($path, json_encode(self::$lootTable, JSON_PRETTY_PRINT));
    }
}