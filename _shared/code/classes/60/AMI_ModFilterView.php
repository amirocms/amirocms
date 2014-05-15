<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModFilterView.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     5.12.0
 */

/**
 * Module filter component view abstraction.
 *
 * @package    ModuleComponent
 * @subpackage View
 * @since      5.12.0
 */
abstract class AMI_ModFilterView extends AMI_ModFormView{
    /**
     * View type
     *
     * @var  string
     * @todo Move this to AMI_View?
     */
    protected $viewType = 'form_filter';

    /**
     * Filter default elemnts template (placeholders)
     *
     * @var array
     * @see AMI_ModPlaceholderView::addPlaceholders()
     * @see AMI_ModPlaceholderView::putPlaceholder()
     */
    protected $aPlaceholders = array(
        '#filter',
            'id_page', 'category', 'datefrom', 'dateto', 'header', 'sticky',
        'filter'
    );

    /**
     *
     * Setting up Model item object.
     *
     * @param  AMI_ModTableItem $oItem  Item model
     * @return AMI_ModFilterView
     */
     /*
    public function setModelItem($oItem){
        $this->oModelItem = $oItem;
        return $this;
    }*/

    /**
     * Returns view data.
     *
     * @return string
     */
    public function get(){
        $this->aScope['scripts'] = '';
        foreach($this->oItem->getViewFields() as $aField){
            if(isset($aField['flt_default'])){
                $aDefaultField = $aField;
                $aDefaultField['type'] = 'default';
                $aDefaultField['name'] = 'default_value_' . $aField['name'];
                $aDefaultField['value'] = $aField['flt_default'];
                $aDefaultField['session_field'] = false;
                $this->addField($aDefaultField);
            }
            $this->addField($aField);
        }
        $this->setPath();
        if(isset($this->aScope['path'])){
            $this->aScope['path'] = $this->parse('path', $this->aScope);
        }
        return parent::get();
    }

    /**
     * Sets 'path' scope variable displaying under filter form.
     *
     * @return void
     * @since  5.14.4
     */
    protected function setPath(){
    }

    /**
     * Model typification.
     *
     * @param  object $oModel  Model
     * @return AMI_ModFilterView
     */
    protected function _setModel($oModel){
        return $this;
    }
}

/**
 * Module admin filter component view abstraction.
 *
 * @package    ModuleComponent
 * @subpackage View
 * @since      5.12.0
 */
abstract class AMI_ModFilterViewAdm extends AMI_ModFilterView{
}

/**
 * AMI_Module module admin filter component view.
 *
 * @package    ModuleComponent
 * @subpackage View
 * @since      5.14.4
 */
abstract class AMI_Module_FilterViewAdm extends AMI_ModFilterViewAdm{
}

/**
 * AMI_CatModule module admin filter component view.
 *
 * @package    ModuleComponent
 * @subpackage View
 * @since      5.14.4
 */
abstract class AMI_CatModule_FilterViewAdm extends AMI_Module_FilterViewAdm{
    /**
     * Filter default elemnts template (placeholders)
     *
     * @var array
     * @see AMI_ModPlaceholderView::addPlaceholders()
     * @see AMI_ModPlaceholderView::putPlaceholder()
     */
    protected $aPlaceholders = array(
        '#filter',
             'header', 'id_page', 'category', 'datefrom', 'dateto', 'sticky',
        'filter'
    );
}
