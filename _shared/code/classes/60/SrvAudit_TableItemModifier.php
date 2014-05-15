<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Module_Audit
 * @version   $Id: SrvAudit_TableItemModifier.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Audit module table item model modifier.
 *
 * @package    Module_Audit
 * @subpackage Model
 * @resource   srv_audit/table/model/item/modifier <code>AMI::getResourceModel('srv_audit/table')->getItem()->getModifier()</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class SrvAudit_TableItemModifier extends AMI_ModTableItemModifier{
    /**
     * Returns default fields and its values on save.
     *
     * @param  bool $onCreate  True on item create, false on update
     * @return array           Array having keys as field names and values as field values
     */
    public function getDefaultsOnSave($onCreate){
        $aDefaults = parent::getDefaultsOnSave($onCreate);
        $aDefaults['append']['ip'] = AMI::getSingleton('env/request')->getEnv('REMOTE_ADDR');
        return $aDefaults;
    }
}
