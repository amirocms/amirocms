<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Hyper_AmiFiles
 * @version   $Id: Hyper_AmiFiles_Cat_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Articles Categories module admin action controller.
 *
 * @package    Hyper_AmiFiles
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class Hyper_AmiFiles_Cat_Adm extends Hyper_AmiMultifeeds_Cat_Adm{
}

/**
 * Module model.
 *
 * @package    Hyper_AmiFiles
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
class Hyper_AmiFiles_Cat_State extends Hyper_AmiMultifeeds_Cat_State{
}

/**
 * Articles Categories module admin filter component action controller.
 *
 * @package    Hyper_AmiFiles
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class Hyper_AmiFiles_Cat_FilterAdm extends Hyper_AmiMultifeeds_Cat_FilterAdm{
}

/**
 * Articles Categories module item list component filter model.
 *
 * @package    Hyper_AmiFiles
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
class Hyper_AmiFiles_Cat_FilterModelAdm extends Hyper_AmiMultifeeds_Cat_FilterModelAdm{
}

/**
 * Articles Categories module admin filter component view.
 *
 * @package    Hyper_AmiFiles
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
class Hyper_AmiFiles_Cat_FilterViewAdm extends Hyper_AmiMultifeeds_Cat_FilterViewAdm{
}

/**
 * Articles Categories module admin form component action controller.
 *
 * @package    Hyper_AmiFiles
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class Hyper_AmiFiles_Cat_FormAdm extends Hyper_AmiMultifeeds_Cat_FormAdm{
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
 * Articles Categories module form component view.
 *
 * @package    Hyper_AmiFiles
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
class Hyper_AmiFiles_Cat_FormViewAdm extends Hyper_AmiMultifeeds_Cat_FormViewAdm{
}

/**
 * Articles Categories module admin list component action controller.
 *
 * @package    Hyper_AmiFiles
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class Hyper_AmiFiles_Cat_ListAdm extends Hyper_AmiMultifeeds_Cat_ListAdm{
    /**
     * Initialization.
     *
     * @return FaqCat_ListAdm
     */
    public function init(){
        parent::init();
        $this->dropActions(self::ACTION_GROUP, array('seo_section'));
        return $this;
    }
}

/**
 * Articles Categories module admin list component view.
 *
 * @package    Hyper_AmiFiles
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
class Hyper_AmiFiles_Cat_ListViewAdm extends Hyper_AmiMultifeeds_Cat_ListViewAdm{
    /**
     * Order column
     *
     * @var string
     */
    protected $orderColumn = 'header';

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
     * @return AMI_CatModule_ListViewAdm
     */
    public function init(){
        parent::init();
        $this->setColumnTensility('header', false);
        // Truncate 'header' column by 50 symbols
        $this->formatColumn(
            'header',
            array($this, 'fmtTruncate'),
            array(
                'length' => 50
            )
        );
        // Truncate 'announce' column by 150 symbols
        $this->formatColumn(
            'announce',
            array($this, 'fmtTruncate'),
            array(
                'length' => 150
            )
        );
        return $this;
    }
}

/**
 * Articles Categories module admin list actions controller.
 *
 * @category   AMI
 * @package    Hyper_AmiFiles
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class Hyper_AmiFiles_Cat_ListActionsAdm extends Hyper_AmiMultifeeds_Cat_ListActionsAdm{
}

/**
 * Articles Categories module admin list group actions controller.
 *
 * @category   AMI
 * @package    Hyper_AmiFiles
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class Hyper_AmiFiles_Cat_ListGroupActionsAdm extends Hyper_AmiMultifeeds_Cat_ListGroupActionsAdm{
}

