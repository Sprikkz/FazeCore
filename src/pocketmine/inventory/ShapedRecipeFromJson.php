<?php



namespace pocketmine\inventory;

use pocketmine\item\Item;
use pocketmine\math\Vector2;
use pocketmine\Server;
use pocketmine\utils\UUID;

class ShapedRecipeFromJson extends ShapedRecipe {
	/** @var Item */
	private $output;

	private $id = null;

	/** @var string[] */
	private $shape = [];

	/** @var Item[][] */
	private $ingredients = [];
	/** @var Vector2[][] */
	private $shapeItems = [];

	/**
	 * @param Item $result
	 * @param int  $height
	 * @param int  $width
	 *
	 * @throws \Exception
	 */
	public function __construct(Item $result, $height, $width){
		for($h = 0; $h < $height; $h++){
			if($width === 0 or $width > 3){
				throw new \InvalidStateException("Crafting rows should be 1, 2, 3 wide, not $width");
			}
			$this->ingredients[] = array_fill(0, $width, null);
		}

		$this->output = clone $result;
	}

	/**
	 * @return int
	 */
	public function getWidth(){
		return count($this->ingredients[0]);
	}

	/**
	 * @return int
	 */
	public function getHeight(){
		return count($this->ingredients);
	}

	/**
	 * @return Item
	 */
	public function getResult(){
		return $this->output;
	}

	/**
	 * @return null
	 */
	public function getId(){
		return $this->id;
	}

	/**
	 * @param UUID $id
	 */
	public function setId(UUID $id){
		if($this->id !== null){
			throw new \InvalidStateException("Id is already set");
		}

		$this->id = $id;
	}

	/**
	 * @param      $x
	 * @param      $y
	 * @param Item $item
	 *
	 * @return $this
	 */
	public function addIngredient($x, $y, Item $item){
		$this->ingredients[$y][$x] = clone $item;
		return $this;
	}

	/**
	 * @param string $key
	 * @param Item   $item
	 *
	 * @return $this
	 * @throws \Exception
	 */
	public function setIngredient($key, Item $item){
		if(!array_key_exists($key, $this->shape)){
			throw new \Exception("Symbol does not appear in the shape: " . $key);
		}

		$this->fixRecipe($key, $item);

		return $this;
	}

	/**
	 * @param $key
	 * @param $item
	 */
	protected function fixRecipe($key, $item){
		foreach($this->shapeItems[$key] as $entry){
			$this->ingredients[$entry->y][$entry->x] = clone $item;
		}
	}

	/**
	 * @return Item[][]
	 */
	public function getIngredientMap(){
		$ingredients = [];
		foreach($this->ingredients as $y => $row){
			$ingredients[$y] = [];
			foreach($row as $x => $ingredient){
				if($ingredient !== null){
					$ingredients[$y][$x] = clone $ingredient;
				}else{
					$ingredients[$y][$x] = Item::get(Item::AIR);
				}
			}
		}

		return $ingredients;
	}

	/**
	 * @param $x
	 * @param $y
	 *
	 * @return null|Item
	 */
	public function getIngredient($x, $y){
		return isset($this->ingredients[$y][$x]) ? $this->ingredients[$y][$x] : Item::get(Item::AIR);
	}

	/**
	 * @return string[]
	 */
	public function getShape(){
		return $this->shape;
	}

	public function registerToCraftingManager(){
		Server::getInstance()->getCraftingManager()->registerShapedRecipe($this);
	}
}