<?php
/**
 * AmiTags hypermodule.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Hyper_AmiTags
 * @version   $Id: Hyper_AmiTags_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiTags hypermodule admin action controller.
 *
 * @package    Hyper_AmiTags
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiTags_Adm extends AMI_Module_Adm{
}

/**
 * Module model.
 *
 * @package    Hyper_AmiTags
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiTags_State extends AMI_ModState{
}

/**
 * AmiTags hypermodule admin filter component action controller.
 *
 * @package    Hyper_AmiTags
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiTags_FilterAdm extends AMI_Module_FilterAdm{
}

/**
 * AmiTags hypermodule item list component filter model.
 *
 * @package    Hyper_AmiTags
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiTags_FilterModelAdm extends AMI_Module_FilterModelAdm{
    /**
     * Common filter fields
     *
     * @var   array
     */
    protected $aCommonFields = array();

    /**
     * Constructor.
     */
    public function __construct(){
        parent::__construct();
        $this->addViewField(
            array(
                'name'          => 'tag',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'tag'
            )
        );
    }
}

/**
 * AmiTags hypermodule admin filter component view.
 *
 * @package    Hyper_AmiTags
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiTags_FilterViewAdm extends AMI_Module_FilterViewAdm{
}

/**
 * AmiTags hypermodule admin form component action controller.
 *
 * @package    Hyper_AmiTags
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiTags_FormAdm extends AMI_Module_FormAdm{
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
 * AmiTags hypermodule admin form component view.
 *
 * @package    Hyper_AmiTags
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiTags_FormViewAdm extends AMI_Module_FormViewAdm{
    /**
     * Initialize fields.
     *
     * @see    AMI_View::init()
     * @return AMI_View
     */
    public function init(){
        $this->addField(array('name' => 'tag', 'type' => 'input'));
        return parent::init();
    }
}

/**
 * AmiTags hypermodule admin list component action controller.
 *
 * @package    Hyper_AmiTags
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiTags_ListAdm extends AMI_Module_ListAdm{
}

/**
 * AmiTags hypermodule admin list component actions controller.
 *
 * @package    Hyper_AmiTags
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiTags_ListActionsAdm extends AMI_Module_ListActionsAdm{
}

/**
 * AmiTags hypermodule admin list component group actions controller.
 *
 * @package    Hyper_AmiTags
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiTags_ListGroupActionsAdm extends AMI_Module_ListGroupActionsAdm{
}

/**
 * AmiTags hypermodule admin list component view.
 *
 * @package    Hyper_AmiTags
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiTags_ListViewAdm extends AMI_Module_ListViewAdm{
    /**
     * Order column
     *
     * @var string
     */
    protected $orderColumn = 'tag';

    /**
     * Order column direction
     *
     * @var bool
     */
    protected $orderDirection = 'asc';

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AMI_Module_ListViewAdm
     */
    public function init(){
        // Init columns
        $this
            ->addColumnType('id', 'hidden')
            ->addColumn('tag')
            ->addColumn('count')
            ->setColumnAlign('count', 'center');

        $this
            ->setColumnTensility('tag')
            ->addSortColumns(
                array(
                    'tag',
                    'count',
                )
            );

        // Truncate 'tag' column by 30 symbols
        $this->formatColumn(
            'tag',
            array($this, 'fmtTruncate'),
            array(
                'length'       => 30
            )
        );

        $this->addScriptCode($this->parse('javascript'));

        return $this;
    }
}
