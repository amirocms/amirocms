<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Module
 * @version   $Id: AMI_Module_Adm.php 44674 2013-11-28 16:45:11Z Leontiev Anton $
 * @since     5.14.4
 */

/**
 * Admin module action controller.
 *
 * @package    Module
 * @subpackage Controller
 * @since      5.14.4
 */
abstract class AMI_Module_Adm extends AMI_Mod{
    /**
     * Path to status message locales
     *
     * @var string
     */
    protected $statusMessagePath = '';

    /**
     * Array of default components
     *
     * @var    array
     * @amidev Temporary
     */
    protected $aDefaultComponents = array('filter', 'list', 'form');

    /**
     * Constructor.
     *
     * @param AMI_Request  $oRequest   Request object
     * @param AMI_Response $oResponse  Response object
     */
    public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){
        $service = $oRequest->get('serve', FALSE);
        if($service !== FALSE && AMI::isResource($this->getModId() . '/serve/' . $service)){
            $oService = AMI::getResource($this->getModId() . '/serve/' . $service);
            $oService->init($this);
            $oService->onModConstructorStart();
        }

        parent::__construct($oRequest, $oResponse);

        $this->addComponents($this->aDefaultComponents);
        AMI_Event::addHandler('on_before_set_data_model_item', array($this, 'handleSetDataModelItem'), $this->getModId());
        AMI_Event::addHandler('dispatch_mod_action', array($this, 'handleRemoveStickyFlag'), $this->getModId());
        if(isset($oService)){
            $oService->onModConstructorEnd();
        }
    }

    /**
     * Set data model item handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleSetDataModelItem($name, array $aEvent, $handlerModId, $srcModId){
        if(isset($aEvent['aData']['id_page'])){
            $pageId = $aEvent['aData']['id_page'];
            if(AMI::issetAndTrueOption($handlerModId, 'use_categories')){
                $pageId = isset($aEvent['aData']['cat_id_page']) ? $aEvent['aData']['cat_id_page'] : $pageId;
            }elseif(
                !AMI::issetAndTrueOption('core', 'multi_page_allowed') ||
                (
                    $aEvent['oItem'] instanceof AMI_CatModTableItem
                        ? !AMI::issetAndTrueOption($aEvent['oTable']->getSubItemsModId(), 'multi_page')
                        : !AMI::issetAndTrueOption($handlerModId, 'multi_page')
                )
            ){
                $pageId = 0;
            }
            $aEvent['aData']['id_page'] = $pageId;
        }
        return $aEvent;
    }

    /**
     * Remove sticky flag if automatic option was set.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleRemoveStickyFlag($name, array $aEvent, $handlerModId, $srcModId){
        $aScope = $aEvent['oRequest']->getScope();
        // full_env action
        if(isset($aScope['ami_full']) && ($aScope['ami_full'] == 1)){
            if(AMI::issetAndTrueProperty($this->getModId(), 'use_special_list_view')){
                $oTable = AMI::getResourceModel($this->getModId() . '/table');
                $oDB = AMI::getSingleton('db');
                $oQuery =
                    DB_Query::getSnippet("SELECT id FROM %s WHERE (`%s` = 1 AND `%s` <= NOW())")
                        ->plain($oTable->getTableName())
                        ->plain($oTable->getFieldName('sticky'))
                        ->plain($oTable->getFieldName('date_sticky_till'));
                $oRS = $oDB->select($oQuery);
                $aIds = array();
                foreach($oRS as $aRecord){
                    $aIds[] = $aRecord['id'];
                }
                if(count($aIds)){
                    $oQuery =
                        DB_Query::getSnippet("UPDATE %s SET `%s` = 0, `%s` = NULL WHERE id IN (%s)")
                            ->plain($oTable->getTableName())
                            ->plain($oTable->getFieldName('sticky'))
                            ->plain($oTable->getFieldName('date_sticky_till'))
                            ->implode($aIds);
                    $oDB->query($oQuery);
                }
            }
        }
        return $aEvent;
    }
}

/**
 * Admin category module action controller.
 *
 * @package    Module
 * @subpackage Controller
 * @since      5.14.4
 */
abstract class AMI_CatModule_Adm extends AMI_Module_Adm{
    /**
     * Constructor.
     *
     * @param AMI_Request  $oRequest   Request object
     * @param AMI_Response $oResponse  Response object
     */
    public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){
        $this->checkUsage();

        parent::__construct($oRequest, $oResponse);
    }

    /**
     * Checks if category usage is turned on in parent module.
     *
     * @return void
     * @since 6.0.2
     */
    protected function checkUsage(){
        $modId = $this->getModId();
        $oDeclarator = AMI_ModDeclarator::getInstance();
        $parentModId = $oDeclarator->getParent($modId);
        if(!AMI::issetAndTrueOption($parentModId, 'use_categories')){
            $this->aDefaultComponents = array('form');
        }
    }
}
