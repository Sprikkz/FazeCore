<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____  
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \ 
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/ 
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_| 
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 * 
 *
*/

/**
 * Different noise generators for level generation
 */

namespace pocketmine\level\generator\noise;


abstract class Noise {
	protected $perm = [];
	protected $offsetX = 0;
	protected $offsetY = 0;
	protected $offsetZ = 0;
	protected $octaves = 8;
	protected $persistence;
	protected $expansion;

	/**
	 * @param $x
	 *
	 * @return int
	 */
	public static function floor($x){
		return $x >= 0 ? (int) $x : (int) ($x - 1);
	}

	/**
	 * @param $x
	 *
	 * @return mixed
	 */
	public static function fade($x){
		return $x * $x * $x * ($x * ($x * 6 - 15) + 10);
	}

	/**
	 * @param $x
	 * @param $y
	 * @param $z
	 *
	 * @return mixed
	 */
	public static function lerp($x, $y, $z){
		return $y + $x * ($z - $y);
	}

	/**
	 * @param $x
	 * @param $x1
	 * @param $x2
	 * @param $q0
	 * @param $q1
	 *
	 * @return float|int
	 */
	public static function linearLerp($x, $x1, $x2, $q0, $q1){
		return (($x2 - $x) / ($x2 - $x1)) * $q0 + (($x - $x1) / ($x2 - $x1)) * $q1;
	}

	/**
	 * @param $x
	 * @param $y
	 * @param $q00
	 * @param $q01
	 * @param $q10
	 * @param $q11
	 * @param $x1
	 * @param $x2
	 * @param $y1
	 * @param $y2
	 *
	 * @return float|int
	 */
	public static function bilinearLerp($x, $y, $q00, $q01, $q10, $q11, $x1, $x2, $y1, $y2){
		$dx1 = (($x2 - $x) / ($x2 - $x1));
		$dx2 = (($x - $x1) / ($x2 - $x1));

		return (($y2 - $y) / ($y2 - $y1)) * (
			$dx1 * $q00 + $dx2 * $q10
		) + (($y - $y1) / ($y2 - $y1)) * (
			$dx1 * $q01 + $dx2 * $q11
		);
	}

	/**
	 * @param $x
	 * @param $y
	 * @param $z
	 * @param $q000
	 * @param $q001
	 * @param $q010
	 * @param $q011
	 * @param $q100
	 * @param $q101
	 * @param $q110
	 * @param $q111
	 * @param $x1
	 * @param $x2
	 * @param $y1
	 * @param $y2
	 * @param $z1
	 * @param $z2
	 *
	 * @return float|int
	 */
	public static function trilinearLerp($x, $y, $z, $q000, $q001, $q010, $q011, $q100, $q101, $q110, $q111, $x1, $x2, $y1, $y2, $z1, $z2){
		$dx1 = (($x2 - $x) / ($x2 - $x1));
		$dx2 = (($x - $x1) / ($x2 - $x1));
		$dy1 = (($y2 - $y) / ($y2 - $y1));
		$dy2 = (($y - $y1) / ($y2 - $y1));

		return (($z2 - $z) / ($z2 - $z1)) * (
				$dy1 * (
					$dx1 * $q000 + $dx2 * $q100
				) + $dy2 * (
					$dx1 * $q001 + $dx2 * $q101
				)
			) + (($z - $z1) / ($z2 - $z1)) * (
				$dy1 * (
					$dx1 * $q010 + $dx2 * $q110
				) + $dy2 * (
					$dx1 * $q011 + $dx2 * $q111
				)
			);
	}

	/**
	 * @param $hash
	 * @param $x
	 * @param $y
	 * @param $z
	 *
	 * @return mixed
	 */
	public static function grad($hash, $x, $y, $z){
		$hash &= 15;
		$u = $hash < 8 ? $x : $y;
		$v = $hash < 4 ? $y : (($hash === 12 or $hash === 14) ? $x : $z);

		return (($hash & 1) === 0 ? $u : -$u) + (($hash & 2) === 0 ? $v : -$v);
	}

	/**
	 * @param $x
	 * @param $z
	 *
	 * @return mixed
	 */
	abstract public function getNoise2D($x, $z);

	/**
	 * @param $x
	 * @param $y
	 * @param $z
	 *
	 * @return mixed
	 */
	abstract public function getNoise3D($x, $y, $z);

	/**
	 * @param float $x
	 * @param float $z
	 * @param bool  $normalized
	 *
	 * @return float
	 */
	public function noise2D($x, $z, $normalized = false){
		$result = 0;
		$amp = 1;
		$freq = 1;
		$max = 0;

		$x *= $this->expansion;
		$z *= $this->expansion;

		for($i = 0; $i < $this->octaves; ++$i){
			$result += $this->getNoise2D($x * $freq, $z * $freq) * $amp;
			$max += $amp;
			$freq *= 2;
			$amp *= $this->persistence;
		}

		if($normalized === true){
			$result /= $max;
		}

		return $result;
	}

	/**
	 * @param float $x
	 * @param float $y
	 * @param float $z
	 * @param bool  $normalized
	 *
	 * @return float
	 */
	public function noise3D($x, $y, $z, $normalized = false){
		$result = 0;
		$amp = 1;
		$freq = 1;
		$max = 0;

		$x *= $this->expansion;
		$y *= $this->expansion;
		$z *= $this->expansion;

		for($i = 0; $i < $this->octaves; ++$i){
			$result += $this->getNoise3D($x * $freq, $y * $freq, $z * $freq) * $amp;
			$max += $amp;
			$freq *= 2;
			$amp *= $this->persistence;
		}

		if($normalized === true){
			$result /= $max;
		}

		return $result;
	}

	/**
	 * @return \SplFixedArray|float[]
	 * @phpstan-return \SplFixedArray<float>
	 */
	public function getFastNoise1D(int $xSize, int $samplingRate, int $x, int $y, int $z) : \SplFixedArray{
		if($samplingRate === 0){
			throw new \InvalidArgumentException("samplingRate cannot be 0");
		}
		if($xSize % $samplingRate !== 0){
			throw new \InvalidArgumentException("xSize % samplingRate must return 0");
		}

		$noiseArray = new \SplFixedArray($xSize + 1);

		for($xx = 0; $xx <= $xSize; $xx += $samplingRate){
			$noiseArray[$xx] = $this->noise3D($xx + $x, $y, $z);
		}

		for($xx = 0; $xx < $xSize; ++$xx){
			if($xx % $samplingRate !== 0){
				$nx = (int) ($xx / $samplingRate) * $samplingRate;
				$noiseArray[$xx] = self::linearLerp($xx, $nx, $nx + $samplingRate, $noiseArray[$nx], $noiseArray[$nx + $samplingRate]);
			}
		}

		return $noiseArray;
	}

	/**
	 * @return \SplFixedArray|float[][]
	 * @phpstan-return \SplFixedArray<\SplFixedArray<float>>
	 */
	public function getFastNoise2D(int $xSize, int $zSize, int $samplingRate, int $x, int $y, int $z) : \SplFixedArray{
		assert($samplingRate !== 0, new \InvalidArgumentException("samplingRate cannot be 0"));

		assert($xSize % $samplingRate === 0, new \InvalidArgumentException("xSize % samplingRate must return 0"));
		assert($zSize % $samplingRate === 0, new \InvalidArgumentException("zSize % samplingRate must return 0"));

		$noiseArray = new \SplFixedArray($xSize + 1);

		for($xx = 0; $xx <= $xSize; $xx += $samplingRate){
			$noiseArray[$xx] = new \SplFixedArray($zSize + 1);
			for($zz = 0; $zz <= $zSize; $zz += $samplingRate){
				$noiseArray[$xx][$zz] = $this->noise3D($x + $xx, $y, $z + $zz);
			}
		}

		for($xx = 0; $xx < $xSize; ++$xx){
			if($xx % $samplingRate !== 0){
				$noiseArray[$xx] = new \SplFixedArray($zSize + 1);
			}

			for($zz = 0; $zz < $zSize; ++$zz){
				if($xx % $samplingRate !== 0 || $zz % $samplingRate !== 0){
					$nx = (int) ($xx / $samplingRate) * $samplingRate;
					$nz = (int) ($zz / $samplingRate) * $samplingRate;
					$noiseArray[$xx][$zz] = Noise::bilinearLerp(
						$xx, $zz, $noiseArray[$nx][$nz], $noiseArray[$nx][$nz + $samplingRate],
						$noiseArray[$nx + $samplingRate][$nz], $noiseArray[$nx + $samplingRate][$nz + $samplingRate],
						$nx, $nx + $samplingRate, $nz, $nz + $samplingRate
					);
				}
			}
		}

		return $noiseArray;
	}

	/**
	 * @return float[][][]
	 */
	public function getFastNoise3D(int $xSize, int $ySize, int $zSize, int $xSamplingRate, int $ySamplingRate, int $zSamplingRate, int $x, int $y, int $z) : array{

		assert($xSamplingRate !== 0, new \InvalidArgumentException("xSamplingRate cannot be 0"));
		assert($zSamplingRate !== 0, new \InvalidArgumentException("zSamplingRate cannot be 0"));
		assert($ySamplingRate !== 0, new \InvalidArgumentException("ySamplingRate cannot be 0"));

		assert($xSize % $xSamplingRate === 0, new \InvalidArgumentException("xSize % xSamplingRate must return 0"));
		assert($zSize % $zSamplingRate === 0, new \InvalidArgumentException("zSize % zSamplingRate must return 0"));
		assert($ySize % $ySamplingRate === 0, new \InvalidArgumentException("ySize % ySamplingRate must return 0"));

		$noiseArray = array_fill(0, $xSize + 1, array_fill(0, $zSize + 1, []));

		for($xx = 0; $xx <= $xSize; $xx += $xSamplingRate){
			for($zz = 0; $zz <= $zSize; $zz += $zSamplingRate){
				for($yy = 0; $yy <= $ySize; $yy += $ySamplingRate){
					$noiseArray[$xx][$zz][$yy] = $this->noise3D($x + $xx, $y + $yy, $z + $zz, true);
				}
			}
		}

		/**
		 * The following code originally called trilinearLerp() in a loop, but it was later inlined to elide function
		 * call overhead.
		 * Later, it became apparent that some of the logic was being repeated unnecessarily in the inner loop, so the
		 * code was changed further to avoid this, which produced visible performance improvements.
		 *
		 * In any language with a compiler, a compiler would most likely have noticed that these optimisations could be
		 * made and made these changes automatically, but in PHP we don't have a compiler, so the task falls to us.
		 *
		 * @see Noise::trilinearLerp()
		 */
		for($xx = 0; $xx < $xSize; ++$xx){
			$nx = (int) ($xx / $xSamplingRate) * $xSamplingRate;
			$nnx = $nx + $xSamplingRate;

			$dx1 = (($nnx - $xx) / ($nnx - $nx));
			$dx2 = (($xx - $nx) / ($nnx - $nx));

			for($zz = 0; $zz < $zSize; ++$zz){
				$nz = (int) ($zz / $zSamplingRate) * $zSamplingRate;
				$nnz = $nz + $zSamplingRate;

				$dz1 = ($nnz - $zz) / ($nnz - $nz);
				$dz2 = ($zz - $nz) / ($nnz - $nz);

				for($yy = 0; $yy < $ySize; ++$yy){
					if($xx % $xSamplingRate !== 0 || $zz % $zSamplingRate !== 0 || $yy % $ySamplingRate !== 0){
						$ny = (int) ($yy / $ySamplingRate) * $ySamplingRate;
						$nny = $ny + $ySamplingRate;

						$dy1 = (($nny - $yy) / ($nny - $ny));
						$dy2 = (($yy - $ny) / ($nny - $ny));

						$noiseArray[$xx][$zz][$yy] = $dz1 * (
								$dy1 * (
									$dx1 * $noiseArray[$nx][$nz][$ny] + $dx2 * $noiseArray[$nnx][$nz][$ny]
								) + $dy2 * (
									$dx1 * $noiseArray[$nx][$nz][$nny] + $dx2 * $noiseArray[$nnx][$nz][$nny]
								)
							) + $dz2 * (
								$dy1 * (
									$dx1 * $noiseArray[$nx][$nnz][$ny] + $dx2 * $noiseArray[$nnx][$nnz][$ny]
								) + $dy2 * (
									$dx1 * $noiseArray[$nx][$nnz][$nny] + $dx2 * $noiseArray[$nnx][$nnz][$nny]
								)
							);
					}
				}
			}
		}

		return $noiseArray;
	}

	/**
	 * @param $x
	 * @param $y
	 * @param $z
	 */
	public function setOffset($x, $y, $z){
		$this->offsetX = $x;
		$this->offsetY = $y;
		$this->offsetZ = $z;
	}
}