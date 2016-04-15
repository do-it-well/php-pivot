<?php
namespace DoItWell;

class Generator implements \Iterator {
	protected $callable;
	protected $final_value;
	protected $position;
	protected $current;
	protected $invalid;

	public function __construct($callable, $final_value = FALSE){
		$this->callable = $callable;
		$this->final_value = $final_value;
		$this->rewind();
	}

	public function current(){
		if( $this->invalid ) return NULL;
		return $this->current;
	}

	public function key(){
		if( $this->invalid ) return NULL;
		return $this->position;
	}

	public function next(){
		if( $this->invalid ) return;
		$this->position++;
	}

	public function rewind(){
		$this->position = 0;
		$this->invalid = FALSE;
	}

	public function valid(){
		if( $this->invalid ) return FALSE;

		$this->current = call_user_func($this->callable, $this->position === 0);
		return $this->current !== $this->final_value;
	}
}
