<?php

namespace slapit\cmd;

class CmdArgMap{
	/** @var string[] */
	private $required = [];
	/** @var string[]|bool[] */
	private $args = [];
	public function __construct(array $args){
		for($i = 0; $i < count($args); $i++){
			if($args[$i]{0} === ","){
				$this->args[strtolower(substr($args[$i], 1))] = true;
			}
			elseif(($argName = ltrim($args[$i], ".")) !== $args[$i]){
				$cnt = strlen($args[$i]) - strlen($argName);
				$subargs = [];
				for($j = 0; $j < $cnt; $j++){
					$subargs[] = $args[$i++];
				}
				$subarg = implode(" ", $subargs);
				if(is_numeric($subarg)){
					$subarg = floatval($subarg);
				}
				$this->args[strtolower($argName)] = $subarg;
			}
			else{
				$this->required[] = $args[$i];
			}
		}
	}
	/**
	 * @param int $offset
	 * @param bool $throwEx
	 * @return null|string
	 */
	public function getReqArg($offset, $throwEx = false){
		return isset($this->required[$offset]) ? $this->required[$offset] :
			($throwEx ? $this->throwEx(new \UnderflowException()):null);
	}
	/**
	 * @param string $key
	 * @param mixed $default
	 * @return bool|string|mixed
	 */
	public function getOptArg($key, $default = null){
		$key = strtolower($key);
		return isset($this->args[$key]) ? $this->args[$key]:$default;
	}
	/**
	 * @param \Exception $exception
	 * @return null
	 * @throws \Exception
	 */
	public function throwEx(\Exception $exception){
		throw $exception;
		/**  @noinspection PhpUnreachableStatementInspection */
		return null;
	}
}
