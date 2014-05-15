<?php
/**
 * AmiFiles hypermodule.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Hyper_AmiFiles
 * @version   $Id: Hyper_AmiFiles_TableItemModifier.php 45064 2013-12-05 09:27:10Z Leontiev Anton $
 * @since     6.0.2
 */

/**
 * AmiFiles hypermodule category table item model modifier.
 *
 * @package    Hyper_AmiFiles
 * @subpackage Model
 * @since      6.0.2
 */
abstract class Hyper_AmiFiles_TableItemModifier extends AMI_Module_TableItemModifier{
    /**
     * Returns default fields and its values on save.
     *
     * @param  bool $onCreate  True on item create, false on update
     * @return array           Array having keys as field names and values as field values
     */
    public function getDefaultsOnSave($onCreate){
        $aDefaults = parent::getDefaultsOnSave($onCreate);
        if(isset($aDefaults['overwrite'])){
            unset($aDefaults['overwrite']['date_modified']);
        }
        $aDefaults['overwrite']['modified_date'] = date('Y-m-d H:i:s');
        return $aDefaults;
    }
}
