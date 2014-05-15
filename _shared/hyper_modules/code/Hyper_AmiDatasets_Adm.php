<?php
/**
 * AmiDatasets hypermodule.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Hyper_AmiDatasets
 * @version   $Id: Hyper_AmiDatasets_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiDatasets hypermodule admin action controller.
 *
 * @package    Hyper_AmiDatasets
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiDatasets_Adm extends AMI_Module_Adm{
}

/**
 * Module model.
 *
 * @package    Hyper_AmiDatasets
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiDatasets_State extends AMI_ModState{
}

/**
 * AmiDatasets hypermodule admin filter component action controller.
 *
 * @package    Hyper_AmiDatasets
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiDatasets_FilterAdm extends AMI_Module_FilterAdm{
}

/**
 * AmiDatasets hypermodule item list component filter model.
 *
 * @package    Hyper_AmiDatasets
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiDatasets_FilterModelAdm extends AMI_Filter{
    /**
     * Constructor.
     */
    public function __construct(){
        $oService = AmiExt_CustomFields_Service::getInstance();
        $aSelect = array();
        foreach($oService->getAllowedModules() as $modId => $caption){
            $aSelect[] = array(
                'name'  => $caption,
                'value' => $modId
             );
        }
        $this->addViewField(
            array(
                'name'          => 'module',
                'type'          => 'select',
                'flt_type'      => 'select',
                'flt_condition' => '=',
                'flt_default'   => '',
                'data'          => $aSelect,
                'not_selected'  => array('id' => '', 'caption' => 'all')
            )
        );

        $this->addViewField(
            array(
                'name'          => 'name',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'name'
            )
        );
    }
}

/**
 * AmiDatasets hypermodule admin filter component view.
 *
 * @package    Hyper_AmiDatasets
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiDatasets_FilterViewAdm extends AMI_Module_FilterViewAdm{
}

/**
 * AmiDatasets hypermodule admin form component action controller.
 *
 * @package    Hyper_AmiDatasets
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiDatasets_FormAdm extends AMI_Module_FormAdm{
    /**
     * Returns true if component needs to be started always in full environment.
     *
     * @return bool
     */
    public function isFullEnv(){
        return FALSE;
    }
}

/**
 * AmiDatasets hypermodule admin form component view.
 *
 * @package    Hyper_AmiDatasets
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiDatasets_FormViewAdm extends AMI_Module_FormViewAdm{
}

/**
 * AmiDatasets hypermodule admin list component action controller.
 *
 * @package    Hyper_AmiDatasets
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiDatasets_ListAdm extends AMI_ModListAdm{
    /**
     * Initialization.
     *
     * @return Hyper_AmiDatasets_ListAdm
     */
    public function init(){
        $this->addGroupActions(
            array(
                array(self::REQUIRE_FULL_ENV . 'delete', 'delete_section')
            )
        );
        parent::init();
        return $this;
    }
}

/**
 * AmiDatasets hypermodule admin list component view.
 *
 * @package    Hyper_AmiDatasets
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiDatasets_ListViewAdm extends AMI_ModListView_JSON{
    /**
     * Order column
     *
     * @var string
     */
    protected $orderColumn = 'name';

    /**
     * Order column direction
     *
     * @var bool
     */
    protected $orderDirection = 'asc';

    /**
     * Column formatter.
     *
     * Converts module id to its caption.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return string
     * @see    AMI_ModListView::formatColumn()
     */
    protected function fmtModule($value, array $aArgs){
        $value = AmiExt_CustomFields_Service::getInstance()->getModCaption($value);
        return $value;
    }
}

/**
 * AmiDatasets hypermodule admin list component actions controller.
 *
 * @package    Hyper_AmiDatasets
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
/*
/*
abstract class Hyper_AmiDatasets_ListActionsAdm extends AMI_ModListActions{
}
*/

/**
 * AmiDatasets hypermodule admin list component group actions controller.
 *
 * @package    Hyper_AmiDatasets
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiDatasets_ListGroupActionsAdm extends AMI_ModListGroupActions{
}
