<?php
/**
 * AmiClean/Webservice configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  Config
 * @package   Config_AmiClean_Webservice
 */

/**
 * AmiClean/Webservice configuration table model.
 *
 * @package    Config_AmiClean_Webservice
 * @subpackage Model
 */
class AmiClean_Webservice_Table extends Hyper_AmiClean_Table{
    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model
     */
    public function __construct(array $aAttributes = array()){
        parent::__construct($aAttributes);

        $aRemap = array(
            'id'      => 'id',
            'header'  => 'header',
            'modules' => 'modules',
            'is_sys'  => 'is_sys',
            'id_user' => 'id_user',
            'api_key' => 'api_key',
            'sublink' => self::FIELD_DOESNT_EXIST,
            'sm_data' => self::FIELD_DOESNT_EXIST,
            'active'  => 'public'
        );
        $this->addFieldsRemap($aRemap);
    }
}

/**
 * AmiClean/Webservice configuration table item model.
 *
 * @package    Config_AmiClean_Webservice
 * @subpackage Model
 */
class AmiClean_Webservice_TableItem extends Hyper_AmiClean_TableItem{
}

/**
 * AmiClean/Webservice configuration table list model.
 *
 * @package    Config_AmiClean_Webservice
 * @subpackage Model
 */
class AmiClean_Webservice_TableList extends Hyper_AmiClean_TableList{
}
