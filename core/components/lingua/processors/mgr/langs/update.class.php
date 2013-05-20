<?php

class LangsUpdateProcessor extends modObjectUpdateProcessor {

	public $classKey = 'Langs';
	public $languageTopics = array('lingua:cmp');
	public $objectType = 'lingua.LangsUpdate';

}

return 'LangsUpdateProcessor';