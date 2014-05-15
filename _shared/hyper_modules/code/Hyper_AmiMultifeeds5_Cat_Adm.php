<?php
/**
 * AmiMultifeeds5 hypermodule.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Hyper_AmiMultifeeds5
 * @version   $Id: Hyper_AmiMultifeeds5_Cat_Adm.php 42166 2013-10-10 12:29:32Z Leontiev Anton $
 * @since     x.x.x
 * @amidev
 */

/**
 * AmiMultifeeds5 hypermodule category admin action controller.
 *
 * @package    Hyper_AmiMultifeeds5
 * @subpackage Controller
 * @since      x.x.x
 * @amidev
 */
abstract class Hyper_AmiMultifeeds5_Cat_Adm extends AMI_CatModule_Adm{
}

/**
 * AmiMultifeeds5 hypermodule category module model.
 *
 * @package    Hyper_AmiMultifeeds5
 * @subpackage Model
 * @since      x.x.x
 * @amidev
 */
abstract class Hyper_AmiMultifeeds5_Cat_State extends AMI_ModState{
}

/**
 * AmiMultifeeds5 hypermodule category admin filter component action controller.
 *
 * @package    Hyper_AmiMultifeeds5
 * @subpackage Controller
 * @since      x.x.x
 * @amidev
 */
abstract class Hyper_AmiMultifeeds5_Cat_FilterAdm extends AMI_CatModule_FilterAdm{
}

/**
 * AmiMultifeeds5 hypermodule category item list component filter model.
 *
 * @package    Hyper_AmiMultifeeds5
 * @subpackage Model
 * @since      x.x.x
 * @amidev
 */
abstract class Hyper_AmiMultifeeds5_Cat_FilterModelAdm extends AMI_CatModule_FilterModelAdm{
}

/**
 * AmiMultifeeds5 hypermodule category admin filter component view.
 *
 * @package    Hyper_AmiMultifeeds5
 * @subpackage View
 * @since      x.x.x
 * @amidev
 */
abstract class Hyper_AmiMultifeeds5_Cat_FilterViewAdm extends AMI_CatModule_FilterViewAdm{
}

/**
 * AmiMultifeeds5 hypermodule category admin form component action controller.
 *
 * @package    Hyper_AmiMultifeeds5
 * @subpackage Controller
 * @since      x.x.x
 * @amidev
 */
abstract class Hyper_AmiMultifeeds5_Cat_FormAdm extends AMI_CatModule_FormAdm{
    /**
     * Returns true if component needs to be started always in full environment.
     *
     * @return bool
     */
    public function isFullEnv(){
        return FALSE;
    }

    /**
     * Initialization.
     *
     * @return Hyper_ArticlesCat_FormAdm
     */
    public function init(){
        AMI::setProperty($this->getModId(), 'picture_cat', 'photoalbum');
        return parent::init();
    }
}

/**
 * AmiMultifeeds5 hypermodule category form component view.
 *
 * @package    Hyper_AmiMultifeeds5
 * @subpackage View
 * @since      x.x.x
 * @amidev
 */
abstract class Hyper_AmiMultifeeds5_Cat_FormViewAdm extends AMI_CatModule_FormViewAdm{
}

/**
 * AmiMultifeeds5 hypermodule category admin list component action controller.
 *
 * @package    Hyper_AmiMultifeeds5
 * @subpackage Controller
 * @since      x.x.x
 * @amidev
 */
abstract class Hyper_AmiMultifeeds5_Cat_ListAdm extends AMI_CatModule_ListAdm{
    /**
     * Initialization.
     *
     * @return Hyper_ArticlesCat_ListAdm
     */
    public function init(){
        $this->listActionsResId = $this->getModId() . '/list_actions/controller/adm';
        $this->listGrpActionsResId = $this->getModId() . '/list_group_actions/controller/adm';
        parent::init();
        // Drop full-env action, replace with fast-env
        $this->dropActions('common', array('edit'));
        $this->addActions(array('edit'));
        return $this;
    }
}

/**
 * AmiMultifeeds5 hypermodule category admin list component view.
 *
 * @package    Hyper_AmiMultifeeds5
 * @subpackage View
 * @since      x.x.x
 * @amidev
 */
abstract class Hyper_AmiMultifeeds5_Cat_ListViewAdm extends AMI_CatModule_ListViewAdm{
}

/**
 * AmiMultifeeds5 hypermodule category admin list actions controller.
 *
 * @category   AMI
 * @package    Hyper_AmiMultifeeds5
 * @subpackage Controller
 * @since      x.x.x
 * @amidev
 */
abstract class Hyper_AmiMultifeeds5_Cat_ListActionsAdm extends AMI_CatModule_ListActionsAdm{
}

/**
 * AmiMultifeeds5 hypermodule category admin list group actions controller.
 *
 * @category   AMI
 * @package    Hyper_AmiMultifeeds5
 * @subpackage Controller
 * @since      x.x.x
 * @amidev
 */
abstract class Hyper_AmiMultifeeds5_Cat_ListGroupActionsAdm extends AMI_CatModule_ListGroupActionsAdm{
}
