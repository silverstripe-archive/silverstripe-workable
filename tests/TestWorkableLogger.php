<?php
require_once 'Zend/Log/Writer/Abstract.php';

class TestWorkableLogger extends Zend_Log_Writer_Abstract {

	public $event;

	public function _write($event) {
		$this->event = $event;
	}


	public static function factory ($config) {
		return new TestWorkableLogger();
	}
}