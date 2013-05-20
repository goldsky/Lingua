<?php

class LangsGetListProcessor extends modObjectGetListProcessor {

    public $classKey = 'Langs';
    public $languageTopics = array('lingua:cmp');
    public $defaultSortField = 'id';
    public $defaultSortDirection = 'ASC';
    public $objectType = 'lingua.LangsGetList';

    public function prepareQueryBeforeCount(xPDOQuery $c) {
        $query = $this->getProperty('query');
        if (!empty($query)) {
            $c->where(array(
                'local_name:LIKE' => '%' . $query . '%',
                'OR:lang_code:LIKE' => '%' . $query . '%',
                'OR:lcid_string:LIKE' => '%' . $query . '%',
                'OR:lcid_dec:LIKE' => '%' . $query . '%',
            ));
        }
        return $c;
    }

}

return 'LangsGetListProcessor';