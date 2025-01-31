<?php



namespace pocketmine\event;

class TranslationContainer extends TextContainer {

	/** @var string[] $params */
	protected $params = [];

	/**
	 * @param string   $text
	 * @param string[] $params
	 */
	public function __construct($text, array $params = []){
		parent::__construct($text);

		$this->setParameters($params);
	}

	/**
	 * @return string[]
	 */
	public function getParameters(){
		return $this->params;
	}

	/**
	 * @param int $i
	 *
	 * @return string
	 */
	public function getParameter($i){
		return $this->params[$i] ?? null;
	}

	/**
	 * @param int    $i
	 * @param string $str
	 */
	public function setParameter($i, $str){
		if($i < 0 or $i > count($this->params)){ //Intended, allow to set the last
            throw new \InvalidArgumentException("Invalid index $i, have " . count($this->params));
		}

		$this->params[(int) $i] = $str;
	}

	/**
	 * @param string[] $params
	 */
	public function setParameters(array $params){
		$i = 0;
		foreach($params as $str){
			$this->params[$i] = $str;

			++$i;
		}
	}
}