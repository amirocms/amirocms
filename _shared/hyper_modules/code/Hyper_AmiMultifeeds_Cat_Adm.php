<?php
/**
 * AmiMultifeeds hypermodule.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Hyper_AmiMultifeeds
 * @version   $Id: Hyper_AmiMultifeeds_Cat_Adm.php 45064 2013-12-05 09:27:10Z Leontiev Anton $
 * @since     6.0.2
 */

/**
 * AmiMultifeeds hypermodule category admin action controller.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage Controller
 * @since      6.0.2
 */
abstract class Hyper_AmiMultifeeds_Cat_Adm extends AMI_CatModule_Adm{
}

/**
 * AmiMultifeeds hypermodule category module model.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage Model
 * @since      6.0.2
 */
abstract class Hyper_AmiMultifeeds_Cat_State extends AMI_ModState{
}

/**
 * AmiMultifeeds hypermodule category admin filter component action controller.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage Controller
 * @since      6.0.2
 */
abstract class Hyper_AmiMultifeeds_Cat_FilterAdm extends AMI_CatModule_FilterAdm{
}

/**
 * AmiMultifeeds hypermodule category item list component filter model.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage Model
 * @since      6.0.2
 */
abstract class Hyper_AmiMultifeeds_Cat_FilterModelAdm extends AMI_CatModule_FilterModelAdm{
}

/**
 * AmiMultifeeds hypermodule category admin filter component view.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage View
 * @since      6.0.2
 */
abstract class Hyper_AmiMultifeeds_Cat_FilterViewAdm extends AMI_CatModule_FilterViewAdm{
}

/**
 * AmiMultifeeds hypermodule category admin form component action controller.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage Controller
 * @since      6.0.2
 */
abstract class Hyper_AmiMultifeeds_Cat_FormAdm extends AMI_CatModule_FormAdm{
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
 * AmiMultifeeds hypermodule category form component view.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage View
 * @since      6.0.2
 */
abstract class Hyper_AmiMultifeeds_Cat_FormViewAdm extends AMI_CatModule_FormViewAdm{
}

/**
 * AmiMultifeeds hypermodule category admin list component action controller.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage Controller
 * @since      6.0.2
 */
abstract class Hyper_AmiMultifeeds_Cat_ListAdm extends AMI_CatModule_ListAdm{
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
 * AmiMultifeeds hypermodule category admin list component view.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage View
 * @since      6.0.2
 */
abstract class Hyper_AmiMultifeeds_Cat_ListViewAdm extends AMI_CatModule_ListViewAdm{
}

/**
 * AmiMultifeeds hypermodule category admin list actions controller.
 *
 * @category   AMI
 * @package    Hyper_AmiMultifeeds
 * @subpackage Controller
 * @since      6.0.2
 */
abstract class Hyper_AmiMultifeeds_Cat_ListActionsAdm extends AMI_CatModule_ListActionsAdm{
}

/**
 * AmiMultifeeds hypermodule category admin list group actions controller.
 *
 * @category   AMI
 * @package    Hyper_AmiMultifeeds
 * @subpackage Controller
 * @since      6.0.2
 */
abstract class Hyper_AmiMultifeeds_Cat_ListGroupActionsAdm extends AMI_CatModule_ListGroupActionsAdm{
}
