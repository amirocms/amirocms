<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Environment
 * @version   $Id$
 * @since     6.0.2
 */

/**
 * Session table model.
 *
 * @package    Environment
 * @subpackage Model
 * @resource   env/session/table/model <code>AMI::getResourceModel('env/session/table')*</code>
 * @since      6.0.2
 */
class Session_Table extends AMI_ModTable{
    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model 
     */
    public function __construct(array $aAttributes = array()){
        parent::__construct($aAttributes);

        $this->tableName = 'cms_sessions';
        $this->addSystemFields(array('token'));

        $aRemap = array();
        $this->addFieldsRemap($aRemap);
    }
}

/**
 * Session table item model.
 *
 * @package    Environment
 * @subpackage Model
 * @resource   env/session/table/model/item <code>AMI::getResourceModel('env/session/table')->getItem()*</code>
 * @since      6.0.2
 */
class Session_TableItem extends AMI_Module_TableItem{
    /**
     * Allow to save model flag.
     *
     * @var    bool
     * @amidev Temporary
     */
    protected $bAllowSave = false;
}

/**
 * Session table list model.
 *
 * @package    Environment
 * @subpackage Model
 * @resource   env/session/table/model/list <code>AMI::getResourceModel('env/session/table')->getList()*</code>
 * @since      6.0.2
 */
class Session_TableList extends AMI_ModTableList{
}
