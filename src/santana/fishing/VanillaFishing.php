<?php

declare(strict_types=1);

namespace santana\fishing;

use pocketmine\data\bedrock\item\ItemTypeNames;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use ReflectionClass;
use santana\fishing\item\FishingRod;

function chance(float $chance): bool
{
    $string = strrchr(strval($chance), ".");
    if ($string == false) {
        return mt_rand(1, 100) <= $chance;
    }
    $count = strlen(substr($string, 1));
    $multiply = intval("1" . str_repeat("0", $count));
    return mt_rand(1, (100 * $multiply)) <= ($chance * $multiply);
}

final class VanillaFishing extends PluginBase
{
    /**
     * @var array
     */
    private array $lootTable = [];

    /**
     * @var array
     */
    private array $fishing = [];

    use SingletonTrait {
        setInstance as private;
        getInstance as private getSingletonInstance;
    }

    /**
     * @return VanillaFishing
     */
    public static function getInstance(): VanillaFishing
    {
        return self::getSingletonInstance();
    }

    /**
     * @return array
     */
    public function getLootTable(): array
    {
        return $this->lootTable;
    }

    /**
     * @param array $lootTable
     */
    public function setLootTable(array $lootTable): void
    {
        $this->lootTable = $lootTable;
    }

    /**
     * @return array
     */
    public function getFishing(): array
    {
        return $this->fishing;
    }

    /**
     * @param array $fishing
     */
    public function setFishing(array $fishing): void
    {
        $this->fishing = $fishing;
    }

    /**
     * @return Item
     */
    public function getRandomLoot(): Item
    {
        foreach ($this->lootTable as $data) {
            if (chance($data["chance"])) {
                return clone $data["item"];
            }
        }
            return VanillaItems::AIR();
    }

    /**
     * @return void
     */
    protected function onLoad(): void
    {
        self::setInstance($this);
    }

    /**
     * @return void
     */
    protected function onEnable(): void
    {
        //TODO: Register Everything
        $fishingRod = new FishingRod(new ItemIdentifier(ItemTypeIds::FISHING_ROD), "Fishing Rod");
        CreativeInventory::getInstance()->add($fishingRod);
        StringToItemParser::getInstance()->override("fishing_rod", fn() => $fishingRod);
        GlobalItemDataHandlers::getDeserializer()->map(ItemTypeNames::FISHING_ROD, fn() => clone $fishingRod);


        $itemSerializer = GlobalItemDataHandlers::getSerializer();
        $reflIS = new ReflectionClass($itemSerializer);
        $reflProp = $reflIS->getProperty("itemSerializers");
        $reflProp->setAccessible(true);
        $val = $reflProp->getValue($itemSerializer);
        unset($val[$fishingRod->getTypeId()]);
        $val[$fishingRod->getTypeId()][get_class($fishingRod)] =  fn() => new SavedItemData(ItemTypeNames::FISHING_ROD);
        $reflProp->setValue($itemSerializer, $val);


        $reflGIDH = new ReflectionClass(GlobalItemDataHandlers::class);
        $reflProp2 = $reflGIDH->getProperty("itemSerializer");
        $reflProp2->setAccessible(true);
        $reflProp2->setValue(GlobalItemDataHandlers::class, $itemSerializer);

        $this->registerVanillaLoot();
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
     * @param Item $item
     * @param float $chance
     * @return void
     */
    protected function addItemToLootTable(Item $item, float $chance): void
    {
        $this->lootTable[] = [
            "chance" => $chance,
            "item" => $item
        ];
    }
}
