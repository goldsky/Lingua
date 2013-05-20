<?php

class LangsCreateProcessor extends modObjectCreateProcessor {

	public $classKey = 'Langs';
	public $languageTopics = array('lingua:cmp');
	public $objectType = 'lingua.LangsCreate';

	public function beforeSave() {
		$langCode = $this->getProperty('lang_code');
		$lcidString = $this->getProperty('lcid_string');
		$lcidDecimal = $this->getProperty('lcid_dec');

		if (empty($langCode) && empty($lcidString) && empty($lcidDecimal)) {
			$this->addFieldError('lang_code', $this->modx->lexicon('lingua.langs_err_ns_lang_data'));
			$this->addFieldError('lcid_string', $this->modx->lexicon('lingua.langs_err_ns_lang_data'));
			$this->addFieldError('lcid_dec', $this->modx->lexicon('lingua.langs_err_ns_lang_data'));
		} else if ($this->doesAlreadyExist(array('lcid_string' => $lcidString))) {
			$this->addFieldError('lcid_string', $this->modx->lexicon('lingua.langs_err_lcid_string_exists'));
		} else if ($this->doesAlreadyExist(array('lcid_dec' => $lcidDecimal))) {
			$this->addFieldError('lcid_dec', $this->modx->lexicon('lingua.langs_err_lcid_dec_exists'));
		}
		return parent::beforeSave();
	}

}

return 'LangsCreateProcessor';