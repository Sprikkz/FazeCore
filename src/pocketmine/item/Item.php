<?php



/**
 * Все классы предметов
 */

namespace pocketmine\item;

use InvalidArgumentException;
use JsonSerializable;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\Zombie;
use pocketmine\entity\Witch;
use pocketmine\entity\Skeleton;
use pocketmine\entity\PigZombie;
use pocketmine\entity\Spider;
use pocketmine\entity\CaveSpider;
use pocketmine\entity\Silverfish;
use pocketmine\inventory\Fuel;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\level\Level;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\NamedTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\utils\Binary;
use pocketmine\utils\Config;
use RuntimeException;
use SplFixedArray;
use const pocketmine\PATH;

class Item implements ItemIds, JsonSerializable {

    /** @var NBT */
    private static $cachedParser = null;

    /**
     * @param string $tag
     *
     * @return CompoundTag
     */
    protected static function parseCompoundTag(string $tag) : CompoundTag{
        if(self::$cachedParser === null){
            self::$cachedParser = new NBT(NBT::LITTLE_ENDIAN);
        }

        self::$cachedParser->read($tag);
        return self::$cachedParser->getData();
    }

    /**
     * @param CompoundTag $tag
     *
     * @return string
     */
    protected static function writeCompoundTag(CompoundTag $tag) : string{
        if(self::$cachedParser === null){
            self::$cachedParser = new NBT(NBT::LITTLE_ENDIAN);
        }

        self::$cachedParser->setData($tag);
        return self::$cachedParser->write();
    }


    /** @var SplFixedArray */
    public static $list = null;
    /** @var Block|null */
    protected $block;
    /** @var int */
    protected $id;
    /** @var int */
    protected $meta;
    /** @var string */
    private $tags = "";
    /** @var CompoundTag|null */
    private $cachedNBT = null;
    /** @var int */
    public $count;
    /** @var string */
    protected $name;

    /**
     * @param bool $readFromJson
     * @param bool $registerCreativeItems
     */
    public static function init($readFromJson = false, bool $registerCreativeItems = true){
        if(self::$list === null){
            self::$list = new SplFixedArray(65536);
            self::$list[self::ACACIA_DOOR] = AcaciaDoor::class;
            self::$list[self::APPLE] = Apple::class;
            self::$list[self::ARROW] = Arrow::class;

            self::$list[self::BAKED_POTATO] = BakedPotato::class;
            self::$list[self::BED] = Bed::class;
            self::$list[self::BEETROOT] = Beetroot::class;
            self::$list[self::BEETROOT_SEEDS] = BeetrootSeeds::class;
            self::$list[self::BEETROOT_SOUP] = BeetrootSoup::class;
            self::$list[self::BIRCH_DOOR] = BirchDoor::class;
            self::$list[self::BLAZE_POWDER] = BlazePowder::class;
            self::$list[self::BOAT] = Boat::class;
            self::$list[self::BONE] = Bone::class;
            self::$list[self::BOOK] = Book::class;
            self::$list[self::BOW] = Bow::class;
            self::$list[self::BOWL] = Bowl::class;
            self::$list[self::BREAD] = Bread::class;
            self::$list[self::BREWING_STAND] = BrewingStand::class;
            self::$list[self::BRICK] = Brick::class;
            self::$list[self::BUCKET] = Bucket::class;

            self::$list[self::CAKE] = Cake::class;
            self::$list[self::CAMERA] = Camera::class;
            self::$list[self::CARROT] = Carrot::class;
            self::$list[self::CAULDRON] = Cauldron::class;
            self::$list[self::CHAIN_BOOTS] = ChainBoots::class;
            self::$list[self::CHAIN_CHESTPLATE] = ChainChestplate::class;
            self::$list[self::CHAIN_HELMET] = ChainHelmet::class;
            self::$list[self::CHAIN_LEGGINGS] = ChainLeggings::class;
            self::$list[self::CHORUS_FRUIT] = ChorusFruit::class;
            self::$list[self::CLAY] = Clay::class;
            self::$list[self::CLOCK] = Clock::class;
            self::$list[self::COAL] = Coal::class;
            self::$list[self::COMPASS] = Compass::class;
            self::$list[self::COOKED_CHICKEN] = CookedChicken::class;
            self::$list[self::COOKED_FISH] = CookedFish::class;
            self::$list[self::COOKED_MUTTON] = CookedMutton::class;
            self::$list[self::COOKED_PORKCHOP] = CookedPorkchop::class;
            self::$list[self::COOKED_RABBIT] = CookedRabbit::class;
            self::$list[self::COOKIE] = Cookie::class;

            self::$list[self::DARK_OAK_DOOR] = DarkOakDoor::class;
            self::$list[self::DIAMOND] = Diamond::class;
            self::$list[self::DIAMOND_AXE] = DiamondAxe::class;
            self::$list[self::DIAMOND_BOOTS] = DiamondBoots::class;
            self::$list[self::DIAMOND_CHESTPLATE] = DiamondChestplate::class;
            self::$list[self::DIAMOND_HELMET] = DiamondHelmet::class;
            self::$list[self::DIAMOND_HOE] = DiamondHoe::class;
            self::$list[self::DIAMOND_LEGGINGS] = DiamondLeggings::class;
            self::$list[self::DIAMOND_PICKAXE] = DiamondPickaxe::class;
            self::$list[self::DIAMOND_SHOVEL] = DiamondShovel::class;
            self::$list[self::DIAMOND_SWORD] = DiamondSword::class;
            self::$list[self::DRAGONS_BREATH] = DragonsBreath::class;
            self::$list[self::DYE] = Dye::class;

            self::$list[self::EGG] = Egg::class;
            self::$list[self::ELYTRA] = Elytra::class;
            self::$list[self::EMERALD] = Emerald::class;
            self::$list[self::EMPTY_MAP] = EmptyMap::class;
            self::$list[self::ENCHANTED_BOOK] = EnchantedBook::class;
            self::$list[self::ENCHANTED_GOLDEN_APPLE] = EnchantedGoldenApple::class;
            self::$list[self::ENCHANTING_BOTTLE] = EnchantingBottle::class;
            self::$list[self::ENDER_PEARL] = EnderPearl::class;
            self::$list[self::EYE_OF_ENDER] = EyeOfEnder::class;
            self::$list[self::END_CRYSTAL] = EnderCrystal::class;
            self::$list[self::NAMETAG] = Nametag::class;

            self::$list[self::FEATHER] = Feather::class;
            self::$list[self::FERMENTED_SPIDER_EYE] = FermentedSpiderEye::class;
            self::$list[self::FILLED_MAP] = FilledMap::class;
            self::$list[self::FIRE_CHARGE] = FireCharge::class;
            self::$list[self::FISHING_ROD] = FishingRod::class;
            self::$list[self::FLINT] = Flint::class;
            self::$list[self::FLINT] = Flint::class;
            self::$list[self::FLINT_STEEL] = FlintSteel::class;
            self::$list[self::FLOWER_POT] = FlowerPot::class;

            self::$list[self::GLASS_BOTTLE] = GlassBottle::class;
            self::$list[self::GLISTERING_MELON] = GlisteringMelon::class;
            self::$list[self::GLOWSTONE_DUST] = GlowstoneDust::class;
            self::$list[self::GOLDEN_APPLE] = GoldenApple::class;
            self::$list[self::GOLDEN_CARROT] = GoldenCarrot::class;
            self::$list[self::GOLD_AXE] = GoldAxe::class;
            self::$list[self::GOLD_BOOTS] = GoldBoots::class;
            self::$list[self::GOLD_CHESTPLATE] = GoldChestplate::class;
            self::$list[self::GOLD_HELMET] = GoldHelmet::class;
            self::$list[self::GOLD_HOE] = GoldHoe::class;
            self::$list[self::GOLD_INGOT] = GoldIngot::class;
            self::$list[self::GOLD_LEGGINGS] = GoldLeggings::class;
            self::$list[self::GOLD_NUGGET] = GoldNugget::class;
            self::$list[self::GOLD_PICKAXE] = GoldPickaxe::class;
            self::$list[self::GOLD_SHOVEL] = GoldShovel::class;
            self::$list[self::GOLD_SWORD] = GoldSword::class;
            self::$list[self::GUNPOWDER] = Gunpowder::class;

            self::$list[self::HOPPER] = Hopper::class;

            self::$list[self::IRON_AXE] = IronAxe::class;
            self::$list[self::IRON_BOOTS] = IronBoots::class;
            self::$list[self::IRON_CHESTPLATE] = IronChestplate::class;
            self::$list[self::IRON_DOOR] = IronDoor::class;
            self::$list[self::IRON_HELMET] = IronHelmet::class;
            self::$list[self::IRON_HOE] = IronHoe::class;
            self::$list[self::IRON_INGOT] = IronIngot::class;
            self::$list[self::IRON_LEGGINGS] = IronLeggings::class;
            self::$list[self::IRON_PICKAXE] = IronPickaxe::class;
            self::$list[self::IRON_SHOVEL] = IronShovel::class;
            self::$list[self::IRON_SWORD] = IronSword::class;
            self::$list[self::ITEM_FRAME] = ItemFrame::class;

            self::$list[self::JUNGLE_DOOR] = JungleDoor::class;

            self::$list[self::LEATHER] = Leather::class;
            self::$list[self::LEATHER_BOOTS] = LeatherBoots::class;
            self::$list[self::LEATHER_CAP] = LeatherCap::class;
            self::$list[self::LEATHER_PANTS] = LeatherPants::class;
            self::$list[self::LEATHER_TUNIC] = LeatherTunic::class;

            self::$list[self::MAGMA_CREAM] = MagmaCream::class;
            self::$list[self::MELON] = Melon::class;
            self::$list[self::MELON_SEEDS] = MelonSeeds::class;
            self::$list[self::MINECART] = Minecart::class;
            self::$list[self::MINECART_WITH_TNT] = MinecartTNT::class;
            self::$list[self::MUSHROOM_STEW] = MushroomStew::class;

            self::$list[self::NETHER_BRICK] = NetherBrick::class;
            self::$list[self::NETHER_QUARTZ] = NetherQuartz::class;
            self::$list[self::NETHER_STAR] = NetherStar::class;
            self::$list[self::NETHER_WART] = NetherWart::class;

            self::$list[self::PAINTING] = Painting::class;
            self::$list[self::PAPER] = Paper::class;
            self::$list[self::POPPED_CHORUS_FRUIT] = PoppedChorusFruit::class;
            self::$list[self::POTATO] = Potato::class;
            self::$list[self::POTION] = Potion::class;
            self::$list[self::PRISMARINE_CRYSTALS] = PrismarineCrystals::class;
            self::$list[self::PRISMARINE_SHARD] = PrismarineShard::class;
            self::$list[self::PUMPKIN_PIE] = PumpkinPie::class;
            self::$list[self::PUMPKIN_SEEDS] = PumpkinSeeds::class;

            self::$list[self::QUARTZ] = Quartz::class;

            self::$list[self::RABBIT_STEW] = RabbitStew::class;
            self::$list[self::RAW_BEEF] = RawBeef::class;
            self::$list[self::RAW_CHICKEN] = RawChicken::class;
            self::$list[self::RAW_FISH] = Fish::class;
            self::$list[self::RAW_MUTTON] = RawMutton::class;
            self::$list[self::RAW_PORKCHOP] = RawPorkchop::class;
            self::$list[self::RAW_RABBIT] = RawRabbit::class;
            self::$list[self::REDSTONE] = Redstone::class;
            self::$list[self::REPEATER] = Repeater::class;
            self::$list[self::ROTTEN_FLESH] = RottenFlesh::class;

            self::$list[self::SHEARS] = Shears::class;
            self::$list[self::SHULKER_SHELL] = ShulkerShell::class;
            self::$list[self::SIGN] = Sign::class;
            self::$list[self::SKULL] = Skull::class;
            self::$list[self::SLIMEBALL] = Slimeball::class;
            self::$list[self::SNOWBALL] = Snowball::class;
            self::$list[self::SPAWN_EGG] = SpawnEgg::class;
            self::$list[self::SPIDER_EYE] = SpiderEye::class;
            self::$list[self::SPLASH_POTION] = SplashPotion::class;
            self::$list[self::SPRUCE_DOOR] = SpruceDoor::class;
            self::$list[self::STEAK] = Steak::class;
            self::$list[self::STICK] = Stick::class;
            self::$list[self::STONE_AXE] = StoneAxe::class;
            self::$list[self::STONE_HOE] = StoneHoe::class;
            self::$list[self::STONE_PICKAXE] = StonePickaxe::class;
            self::$list[self::STONE_SHOVEL] = StoneShovel::class;
            self::$list[self::STONE_SWORD] = StoneSword::class;
            self::$list[self::STRING] = ItemString::class;
            self::$list[self::SUGAR] = Sugar::class;
            self::$list[self::SUGARCANE] = Sugarcane::class;

            self::$list[self::TOTEM] = Totem::class;

            self::$list[self::WHEAT] = Wheat::class;
            self::$list[self::WHEAT_SEEDS] = WheatSeeds::class;
            self::$list[self::WOODEN_AXE] = WoodenAxe::class;
            self::$list[self::WOODEN_DOOR] = WoodenDoor::class;
            self::$list[self::WOODEN_HOE] = WoodenHoe::class;
            self::$list[self::WOODEN_PICKAXE] = WoodenPickaxe::class;
            self::$list[self::WOODEN_SHOVEL] = WoodenShovel::class;
            self::$list[self::WOODEN_SWORD] = WoodenSword::class;

            for($i = 0; $i < 256; ++$i){
                if(Block::$list[$i] !== null){
                    self::$list[$i] = Block::$list[$i];
                }
            }
        }

        if($registerCreativeItems)
            self::initCreativeItems();
    }

    private static function initCreativeItems(){
		self::clearCreativeItems();

		$creativeItems = new Config(\pocketmine\PATH . "src/pocketmine/resources/creativeitems.json", Config::JSON, []);

		foreach($creativeItems->getAll() as $data){
			$item = Item::jsonDeserialize($data);
			if($item->getName() === "Unknown"){
				continue;
			}

			self::addCreativeItem($item);
		}
	}

    public static function clearCreativeItems(){
        CreativeItemsStorage::getInstance()->clearItems();
    }

    /**
     * @return array
     */
    public static function getCreativeItems() : array{
        return CreativeItemsStorage::getInstance()->getItems();
    }

    /**
     * @param Item $item
     */
    public static function addCreativeItem(Item $item){
        CreativeItemsStorage::getInstance()->addItem($item);
    }

    /**
     * @param Item $item
     */
    public static function removeCreativeItem(Item $item){
        $index = self::getCreativeItemIndex($item);
        if($index !== -1){
            CreativeItemsStorage::getInstance()->removeItemByIndex($index);
        }
    }

    /**
     * @param Item $item
     *
     * @return bool
     */
    public static function isCreativeItem(Item $item) : bool{
        foreach(CreativeItemsStorage::getInstance()->getItems() as $i => $d){
            if($item->equals($d, !$item->isTool())){
                return true;
            }
        }

        return false;
    }

    /**
     * @param $index
     *
     * @return Item
     */
    public static function getCreativeItem(int $index){
        return CreativeItemsStorage::getInstance()->getItemByIndex($index);
    }

    /**
     * @param Item $item
     *
     * @return int
     */
    public static function getCreativeItemIndex(Item $item) : int{
        foreach(self::getCreativeItems() as $i => $d){
            if($item->equals($d, !$item->isTool())){
                return $i;
            }
        }

        return -1;
    }

    /**
     * @param int    $id
     * @param int    $meta
     * @param int    $count
     * @param string $tags
     *
     * @return Item
     */
    public static function get(int $id, int $meta = 0, int $count = 1, string $tags = "") : Item{
        try{
            $class = self::$list[$id];
            if($class === null){
                return (new Item($id, $meta, $count))->setCompoundTag($tags);
            }elseif($id < 256){
                return (new ItemBlock(new $class($meta), $meta, $count))->setCompoundTag($tags);
            }else{
                return (new $class($meta, $count))->setCompoundTag($tags);
            }
        }catch(RuntimeException $e){
            return (new Item($id, $meta, $count))->setCompoundTag($tags);
        }
    }

    /**
     * @param string $str
     * @param bool   $multiple
     *
     * @return Item[]|Item
     */
    public static function fromString(string $str, bool $multiple = false){
        if($multiple){
            $blocks = [];
            foreach(explode(",", $str) as $b){
                $blocks[] = self::fromString($b, false);
            }

            return $blocks;
        }else{
            $b = explode(":", str_replace([" ", "minecraft:"], ["_", ""], trim($str)));
            if(!isset($b[1])){
                $meta = 0;
            }elseif(is_numeric($b[1])){
                $meta = (int) $b[1];
            }else{
                throw new InvalidArgumentException("Не в состоянии распознать \"" . $b[1] . "\" из \"" . $str . "\" как допустимое мета-значение");
            }

            if(is_numeric($b[0])){
                $item = self::get((int) $b[0] & 0xFFFF, $meta);
            }elseif(defined(Item::class . "::" . strtoupper($b[0]))){
                $item = self::get(constant(Item::class . "::" . strtoupper($b[0])), $meta);
            }else{
                throw new InvalidArgumentException("Не удалось решить \"" . $str . "\" к действительному элементу");
            }

            return $item;
        }
    }

    /**
     * Item constructor.
     *
     * @param int    $id
     * @param int    $meta
     * @param int    $count
     * @param string $name
     */
    public function __construct(int $id, int $meta = 0, int $count = 1, string $name = "Unknown"){
        $this->id = $id & 0xffff;
        $this->setDamage($meta);
        $this->count = $count;
        $this->name = $name;
        if(!isset($this->block) and $this->id <= 0xff and isset(Block::$list[$this->id])){
            $this->block = Block::get($this->id, $this->meta);
            $this->name = $this->block->getName();
        }
    }

    /**
     * @deprecated Этот метод принимает сериализованные NBT в сетевом формате.
     * @see Item::setNamedTag()
     *
     * @param $tags
     *
     * @return $this
     */
    public function setCompoundTag($tags){
        if($tags instanceof CompoundTag){
            $this->setNamedTag($tags);
        }elseif(is_string($tags) and strlen($tags) > 0){
            $this->setNamedTag(self::parseCompoundTag($tags));
        }else{
            $this->tags = (string) $tags;
            $this->cachedNBT = null;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getCompoundTag() : string{
        return $this->tags;
    }

    /**
     * @return bool
     */
    public function hasCompoundTag() : bool{
        return $this->tags !== "";
    }

    /**
     * @return bool
     */
    public function hasCustomBlockData() : bool{
        if(!$this->hasCompoundTag()){
            return false;
        }

        $tag = $this->getNamedTag();
        if(isset($tag->BlockEntityTag) and $tag->BlockEntityTag instanceof CompoundTag){
            return true;
        }

        return false;
    }

    /**
     * @return $this
     */
    public function clearCustomBlockData(){
        if(!$this->hasCompoundTag()){
            return $this;
        }
        $tag = $this->getNamedTag();

        if(isset($tag->BlockEntityTag) and $tag->BlockEntityTag instanceof CompoundTag){
            unset($tag->BlockEntityTag);
            $this->setNamedTag($tag);
        }

        return $this;
    }

    /**
     * @param CompoundTag $compound
     *
     * @return $this
     */
    public function setCustomBlockData(CompoundTag $compound){
        $tags = clone $compound;
        $tags->setName("BlockEntityTag");

        if(!$this->hasCompoundTag()){
            $tag = new CompoundTag("", []);
        }else{
            $tag = $this->getNamedTag();
        }

        $tag->BlockEntityTag = $tags;
        $this->setNamedTag($tag);

        return $this;
    }

    /**
     * @return null
     */
    public function getCustomBlockData(){
        if(!$this->hasCompoundTag()){
            return null;
        }

        $tag = $this->getNamedTag();
        if(isset($tag->BlockEntityTag) and $tag->BlockEntityTag instanceof CompoundTag){
            return $tag->BlockEntityTag;
        }

        return null;
    }

    /**
     * @return bool
     */
    public function hasEnchantments() : bool{
        if(!$this->hasCompoundTag()){
            return false;
        }

        $tag = $this->getNamedTag();
        if(isset($tag->ench)){
            $tag = $tag->ench;
            if($tag instanceof ListTag){
                return true;
            }
        }

        return false;
    }

    /**
     * @param $id
     *
     * @return Enchantment|null
     */
    public function getEnchantment(int $id){
        if(!$this->hasEnchantments()){
            return null;
        }

        foreach($this->getNamedTag()->ench as $entry){
            if($entry["id"] === $id){
                $e = Enchantment::getEnchantment($entry["id"]);
                $e->setLevel($entry["lvl"]);
                return $e;
            }
        }

        return null;
    }

    /**
     * @param int  $id
     * @param int  $level
     * @param bool $compareLevel
     *
     * @return bool
     */
    public function hasEnchantment(int $id, int $level = 1, bool $compareLevel = false) : bool{
        if($this->hasEnchantments()){
            foreach($this->getEnchantments() as $enchantment){
                if($enchantment->getId() == $id){
                    if($compareLevel){
                        if($enchantment->getLevel() == $level){
                            return true;
                        }
                    }else{
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * @param $id
     *
     * @return Int level|0(for null)
     */
    public function getEnchantmentLevel(int $id){
        if(!$this->hasEnchantments()){
            return 0;
        }

        foreach($this->getNamedTag()->ench as $entry){
            if($entry["id"] === $id){
                $e = Enchantment::getEnchantment($entry["id"]);
                $e->setLevel($entry["lvl"]);
                $E_level = $e->getLevel() > Enchantment::getEnchantMaxLevel($id) ? Enchantment::getEnchantMaxLevel($id) : $e->getLevel();
                return $E_level;
            }
        }

        return 0;
    }

    /**
     * @param int $id
     * @param int $level
     */
    public function removeEnchantment(int $id, int $level = -1){
        if(!$this->hasEnchantments()){
            return;
        }

        $tag = $this->getNamedTag();
        foreach($tag->ench as $k => $entry){
            if($entry["id"] === $id){
                if($level === -1 or $entry["lvl"] === $level){
                    unset($tag->ench[$k]);
                    break;
                }
            }
        }
        $this->setNamedTag($tag);
    }

    public function removeEnchantments(){
        if($this->hasEnchantments()){
            $tag = $this->getNamedTag();
            unset($tag->ench);
            $this->setNamedTag($tag);
        }
    }

    /**
     * @param Enchantment $ench
     */
    public function addEnchantment(Enchantment $ench){
        if(!$this->hasCompoundTag()){
            $tag = new CompoundTag("", []);
        }else{
            $tag = $this->getNamedTag();
        }

        if(!isset($tag->ench)){
            $tag->ench = new ListTag("ench", []);
            $tag->ench->setTagType(NBT::TAG_Compound);
        }

        $found = false;

        foreach($tag->ench as $k => $entry){
            if($entry["id"] === $ench->getId()){
                $tag->ench->{$k} = new CompoundTag("", [
                    "id" => new ShortTag("id", $ench->getId()),
                    "lvl" => new ShortTag("lvl", $ench->getLevel())
                ]);
                $found = true;
                break;
            }
        }

        if(!$found){
            $count = 0;
            foreach($tag->ench as $key => $value){
                if(is_numeric($key)){
                    $count++;
                }
            }
            $tag->ench->{$count + 1} = new CompoundTag("", [
                "id" => new ShortTag("id", $ench->getId()),
                "lvl" => new ShortTag("lvl", $ench->getLevel())
            ]);
        }

        $this->setNamedTag($tag);
    }

    /**
     * @return Enchantment[]
     */
    public function getEnchantments() : array{
        if(!$this->hasEnchantments()){
            return [];
        }

        $enchantments = [];

        foreach($this->getNamedTag()->ench as $entry){
            $e = Enchantment::getEnchantment($entry["id"]);
            $e->setLevel($entry["lvl"]);
            $enchantments[] = $e;
        }

        return $enchantments;
    }

    /**
     * @return bool
     */
    public function hasRepairCost() : bool{
        if(!$this->hasCompoundTag()){
            return false;
        }

        $tag = $this->getNamedTag();
        if(isset($tag->RepairCost)){
            $tag = $tag->RepairCost;
            if($tag instanceof IntTag){
                return true;
            }
        }

        return false;
    }

    /**
     * @return int
     */
    public function getRepairCost() : int{
        if(!$this->hasCompoundTag()){
            return 1;
        }

        $tag = $this->getNamedTag();
        if(isset($tag->display)){
            $tag = $tag->RepairCost;
            if($tag instanceof IntTag){
                return $tag->getValue();
            }
        }

        return 1;
    }


    /**
     * @param int $cost
     *
     * @return $this
     */
    public function setRepairCost(int $cost){
        if($cost === 1){
            $this->clearRepairCost();
        }

        if(!($hadCompoundTag = $this->hasCompoundTag())){
            $tag = new CompoundTag("", []);
        }else{
            $tag = $this->getNamedTag();
        }

        $tag->RepairCost = new IntTag("RepairCost", $cost);

        if(!$hadCompoundTag){
            $this->setCompoundTag($tag);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function clearRepairCost(){
        if(!$this->hasCompoundTag()){
            return $this;
        }
        $tag = $this->getNamedTag();

        if(isset($tag->RepairCost) and $tag->RepairCost instanceof IntTag){
            unset($tag->RepairCost);
            $this->setNamedTag($tag);
        }

        return $this;
    }


    /**
     * @return bool
     */
    public function hasCustomName() : bool{
        if(!$this->hasCompoundTag()){
            return false;
        }

        $tag = $this->getNamedTag();
        if(isset($tag->display)){
            $tag = $tag->display;
            if($tag instanceof CompoundTag and isset($tag->Name) and $tag->Name instanceof StringTag){
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getCustomName() : string{
        if(!$this->hasCompoundTag()){
            return "";
        }

        $tag = $this->getNamedTag();
        if(isset($tag->display)){
            $tag = $tag->display;
            if($tag instanceof CompoundTag and isset($tag->Name) and $tag->Name instanceof StringTag){
                return $tag->Name->getValue();
            }
        }

        return "";
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setCustomName(string $name){
        if($name === ""){
            return $this->clearCustomName();
        }

        if(!$this->hasCompoundTag()){
            $tag = new CompoundTag("", []);
        }else{
            $tag = $this->getNamedTag();
        }

        if(isset($tag->display) and $tag->display instanceof CompoundTag){
            $tag->display->Name = new StringTag("Name", $name);
        }else{
            $tag->display = new CompoundTag("display", [
                "Name" => new StringTag("Name", $name)
            ]);
        }

        $this->setCompoundTag($tag);

        return $this;
    }

    /**
     * @return $this
     */
    public function clearCustomName(){
        if(!$this->hasCompoundTag()){
            return $this;
        }
        $tag = $this->getNamedTag();

        if(isset($tag->display) and $tag->display instanceof CompoundTag){
            unset($tag->display->Name);
            if($tag->display->getCount() === 0){
                unset($tag->display);
            }

            $this->setNamedTag($tag);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getLore() : array{
        $tag = $this->getNamedTagEntry("display");
        if($tag instanceof CompoundTag and isset($tag->Lore) and $tag->Lore instanceof ListTag){
            $lines = [];
            foreach($tag->Lore->getValue() as $line){
                $lines[] = $line->getValue();
            }
            return $lines;
        }
        return [];
    }

    /**
     * @param array $lines
     *
     * @return $this
     */
    public function setLore(array $lines){
        $tag = $this->getNamedTag() ?? new CompoundTag("", []);
        if(!isset($tag->display)){
            $tag->display = new CompoundTag("display", []);
        }
        $tag->display->Lore = new ListTag("Lore");
        $tag->display->Lore->setTagType(NBT::TAG_String);
        $count = 0;
        foreach($lines as $line){
            $tag->display->Lore[$count++] = new StringTag("", $line);
        }

        $this->setNamedTag($tag);

        return $this;
    }


    /**
     * @param $name
     *
     * @return null
     */
    public function getNamedTagEntry($name){
        $tag = $this->getNamedTag();
        if($tag !== null){
            return isset($tag->{$name}) ? $tag->{$name} : null;
        }

        return null;
    }

    public function setNamedTagEntry(NamedTag $new) : void{
        $tag = $this->getNamedTag();
        $tag->{$new->getName()} = $new;
        $this->setNamedTag($tag);
    }

    public function removeNamedTagEntry(string $name) : void{
        $tag = $this->getNamedTag();
        unset($tag->{$name});
        $this->setNamedTag($tag);
    }

    public function getNamedTag() : ?CompoundTag{
        if(!$this->hasCompoundTag()){
            return null;
        }elseif($this->cachedNBT !== null){
            return $this->cachedNBT;
        }
        return $this->cachedNBT = self::parseCompoundTag($this->tags);
    }

    /**
     * @param CompoundTag $tag
     *
     * @return $this|Item
     */
    public function setNamedTag(CompoundTag $tag){
        if($tag->getCount() === 0){
            return $this->clearNamedTag();
        }

        $this->cachedNBT = $tag;
        $this->tags = self::writeCompoundTag($tag);

        return $this;
    }

    /**
     * @return Item
     */
    public function clearNamedTag(){
        return $this->setCompoundTag("");
    }

    /**
     * @return int
     */
    public function getCount() : int{
        return $this->count;
    }

    /**
     * @param int $count
     */
    public function setCount(int $count){
        $this->count = $count;
    }

    /**
     * Извлекает элемент из стека и возвращает его, уменьшая количество стека этого стека элементов на единицу.
     *
     * @return static Клон этого стека предметов, содержащий количество предметов, которые были удалены из этого стека.
     * @throws InvalidArgumentException при попытке вытолкнуть больше элементов, чем в стеке
     */
    public function pop(int $count = 1) : Item{
        if($count > $this->count){
            throw new InvalidArgumentException("Не может поп $count предметы из стопки $this->count");
        }

        $item = clone $this;
        $item->count = $count;

        $this->count -= $count;

        return $item;
    }

    public function isNull() : bool{
        return $this->count <= 0 or $this->id === Item::AIR;
    }

    /**
     * @return string
     */
    final public function getName() : string{
        return $this->hasCustomName() ? $this->getCustomName() : $this->getVanillaName();
    }

    /**
     * Возвращает ванильное имя элемента, игнорируя пользовательские имена.
     * @return string
     */
    public function getVanillaName() : string{
        return $this->name;
    }

    /**
     * @return bool
     */
    final public function canBePlaced() : bool{
        return $this->block !== null and $this->block->canBePlaced();
    }

    /**
     * @return bool
     */
    final public function isPlaceable() : bool{
        return $this->canBePlaced();
    }

    /**
     * @return bool
     */
    public function canBeConsumed() : bool{
        return false;
    }

    /**
     * @param Entity $entity
     *
     * @return bool
     */
    public function canBeConsumedBy(Entity $entity) : bool{
        return $this->canBeConsumed();
    }

    /**
     * @param Entity $entity
     */
    public function onConsume(Entity $entity){
    }

    /**
     * @return Block
     */
    public function getBlock() : Block{
        if($this->block instanceof Block){
            return clone $this->block;
        }else{
            return Block::get(self::AIR);
        }
    }

    /**
     * @return int
     */
    final public function getId() : int{
        return $this->id;
    }

    /**
     * @return int
     */
    final public function getDamage() : int{
        return $this->meta;
    }

    /**
     * @param int $meta
     * @return $this
     */
    public function setDamage(int $meta){
        $this->meta = $meta !== -1 ? $meta & 0x7FFF : -1;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasAnyDamageValue() : bool{
        return $this->meta === -1;
    }

    /**
     * @return int
     */
    public function getMaxStackSize() : int{
        return 64;
    }

    /**
     * @return null
     */
    final public function getFuelTime(){
        if(!isset(Fuel::$duration[$this->id])){
            return null;
        }
        if($this->id !== self::BUCKET or $this->meta === 10){
            return Fuel::$duration[$this->id];
        }

        return null;
    }

    /**
     * Возвращает предмет после сжигания топлива
     */
    public function getFuelResidue() : Item{
        $item = clone $this;
        $item->pop();

        return $item;
    }

    /**
     * @param Entity|Block|Item $object
     *
     * @return bool
     */
    public function useOn($object){
        return false;
    }

    /**
     * @return bool
     */
    public function isTool(){
        return false;
    }

    /**
     * @return int|bool
     */
    public function getMaxDurability(){
        return false;
    }

    /**
     * @return bool
     */
    public function isPickaxe(){
        return false;
    }

    /**
     * @return bool
     */
    public function isAxe(){
        return false;
    }

    /**
     * @return bool
     */
    public function isSword(){
        return false;
    }

    /**
     * @return bool
     */
    public function isShovel(){
        return false;
    }

    /**
     * @return bool
     */
    public function isHoe(){
        return false;
    }

    /**
     * @return bool
     */
    public function isShears(){
        return false;
    }

    /**
     * @return bool
     */
    public function isArmor(){
        return false;
    }

    /**
     * @return bool
     */
    public function getArmorValue(){
        return false;
    }

    /**
     * @return bool
     */
    public function isBoots(){
        return false;
    }

    /**
     * @return bool
     */
    public function isHelmet(){
        return false;
    }

    /**
     * @return bool
     */
    public function isLeggings(){
        return false;
    }

    /**
     * @return bool
     */
    public function isChestplate(){
        return false;
    }

    /**
     * @return int
     */
    public function getAttackDamage(){
        return 1;
    }

    /**
     * @param Entity $target
     *
     * @return float|int
     */
    public function getModifyAttackDamage(Entity $target){
        $rec = $this->getAttackDamage();
        $sharpL = $this->getEnchantmentLevel(Enchantment::TYPE_WEAPON_SHARPNESS);
        if($sharpL > 0){
            $rec += 0.5 * ($sharpL + 1);
        }

        if($target instanceof Skeleton or $target instanceof Zombie or
            $target instanceof Witch or $target instanceof PigZombie
        ){
            //SMITE    wither skeletons
            $rec += 2.5 * $this->getEnchantmentLevel(Enchantment::TYPE_WEAPON_SMITE);

        }elseif($target instanceof Spider or $target instanceof CaveSpider or
            $target instanceof Silverfish
        ){
            //Bane of Arthropods    wither skeletons
            $rec += 2.5 * $this->getEnchantmentLevel(Enchantment::TYPE_WEAPON_ARTHROPODS);

        }
        return $rec;
    }

    /**
     * @param Block  $block
     * @param Player $player
     *
     * @return int
     */
    public function getDestroySpeed(Block $block, Player $player){
        return 1;
    }

    public function useOnAir(Player $player) : void{

    }

    /**
     * @param Level  $level
     * @param Player $player
     * @param Block  $block
     * @param Block  $target
     * @param        $face
     * @param        $fx
     * @param        $fy
     * @param        $fz
     *
     * @return bool
     */
    public function onActivate(Level $level, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
        return false;
    }

    /**
     * @param Item $item
     * @param bool $checkDamage
     * @param bool $checkCompound
     * @param bool $checkCount
     *
     * @return bool
     */
    public final function equals(Item $item, bool $checkDamage = true, bool $checkCompound = true, $checkCount = false) : bool{
        if($this->id === $item->getId() and ($checkDamage === false or $this->getDamage() === $item->getDamage()) and ($checkCount === false or $this->getCount() === $item->getCount())){
            if($checkCompound){
                if($item->getCompoundTag() === $this->getCompoundTag()){
                    return true;
                }elseif($this->hasCompoundTag() and $item->hasCompoundTag()){
                    //Сериализованный NBT не совпал, проверьте кешированное дерево объектов.
                    return NBT::matchTree($this->getNamedTag(), $item->getNamedTag());
                }
            }else{
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    final public function __toString() : string{
        return "Item " . $this->name . " (" . $this->id . ":" . ($this->meta === null ? "?" : $this->meta) . ")x" . $this->count . ($this->hasCompoundTag() ? " tags:" . base64_encode($this->getCompoundTag()) : "");
    }

    /**
     * @return array
     */
    final public function jsonSerialize(){
        return [
            "id" => $this->id,
            "damage" => $this->meta,
            "count" => $this->count, //TODO: separate items and stacks
            "nbt" => $this->tags
        ];
    }
    
    final public static function jsonDeserialize(array $data) : Item{
		return Item::get(
			(int) $data["id"],
			(int) ($data["damage"] ?? 0),
			(int) ($data["count"] ?? 1),
			(string) ($data["nbt"] ?? (isset($data["nbt_hex"]) ? hex2bin($data["nbt_hex"]) : "")) //`nbt` key might contain old raw data
		);
	}

    /**
     * Сериализирует элемент в CompoundTag NBT.
     *
     * @param int    $slot    optional, the inventory slot of the item
     * @param string $tagName the name to assign to the CompoundTag object
     *
     * @return CompoundTag
     */
    public function nbtSerialize(int $slot = -1, string $tagName = "") : CompoundTag{
        $tag = new CompoundTag($tagName, [
            "id" => new ShortTag("id", Binary::signShort($this->id)),
            "Count" => new ByteTag("Count", Binary::signByte($this->count)),
            "Damage" => new ShortTag("Damage", $this->meta),
        ]);

        if($this->hasCompoundTag()){
            $tag->tag = clone $this->getNamedTag();
            $tag->tag->setName("tag");
        }

        if($slot !== -1){
            $tag->Slot = new ByteTag("Slot", $slot);
        }

        return $tag;
    }

    /**
     * Десериализует элемент из NBT CompoundTag
     *
     * @param CompoundTag $tag
     *
     * @return Item
     */
    public static function nbtDeserialize(CompoundTag $tag) : Item{
        if(!isset($tag->id) or !isset($tag->Count)){
            return Item::get(0);
        }

        $count = Binary::unsignByte($tag->Count->getValue());
        $meta = isset($tag->Damage) ? $tag->Damage->getValue() : 0;

        if($tag->id instanceof ShortTag){
            $item = Item::get(Binary::unsignShort($tag->id->getValue()), $meta, $count);
        }elseif($tag->id instanceof StringTag){ //PC item save format
            try{
                $item = Item::fromString($tag->id->getValue());
            }catch(\InvalidArgumentException $e){
                //TODO: improve error handling
                return Item::get(Item::AIR, 0, 0);
            }
            $item->setDamage($meta);
            $item->setCount($count);
        }else{
            throw new InvalidArgumentException("Идентификатор CompoundTag элемента должен быть экземпляром StringTag или ShortTag., " . get_class($tag->id) . " данный");
        }

        if(isset($tag->tag) and $tag->tag instanceof CompoundTag){
            $t = clone $tag->tag;
            $t->setName("");
            $item->setNamedTag($t);
        }

        return $item;
    }

    public function __clone(){
        if($this->block !== null){
            $this->block = clone $this->block;
        }

        $this->cachedNBT = null;
    }

}
