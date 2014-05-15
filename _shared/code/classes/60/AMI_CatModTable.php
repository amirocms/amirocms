<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_CatModTable.php 42102 2013-10-07 11:53:16Z Leontiev Anton $
 * @since     5.14.4
 */

/**
 * Categories module table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * @package    ModuleComponent
 * @subpackage Model
 * @since      5.14.4
 */
class AMI_CatModTable extends AMI_ModTable{
    /**
     * Category items resource table string
     *
     * @var string
     */
    protected $subItemsTableResId;

    /**
     * Category items module id
     *
     * @var string
     */
    protected $subItemsModId;

    /**
     * Return category items resource string.
     *
     * @return string
     */
    public function getSubItemsTableResource(){
        return $this->subItemsTableResId;
    }

    /**
     * Return category items module id.
     *
     * @return string
     */
    public function getSubItemsModId(){
        return $this->subItemsModId;
    }

    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model 
     */
    public function __construct(array $aAttributes = array()){
        if(!$this->subItemsModId){
            $this->subItemsModId = preg_replace('/_cat$/', '', $this->getModId());
            // Temp hack for catalog and clones
            $aCatalogExclusions = array('eshop', 'kb', 'portfolio');
            if(in_array($this->subItemsModId, $aCatalogExclusions)){
                $this->subItemsModId .= '_item';
            }
        }
        if(!$this->subItemsTableResId){
            $this->subItemsTableResId = $this->subItemsModId . '/table';
        }

        parent::__construct($aAttributes);
    }
}

/**
 * Categories module table item model.
 *
 * @package    ModuleComponent
 * @subpackage Model
 * @since      5.14.4
 */
class AMI_CatModTableItem extends AMI_ModTableItem{
    /**
     * Common fields validators
     *
     * @var array
     */
    protected $aCommonFieldsValidators =
        array(
            'lang'     => array('filled'),
            'header'   => array('filled'),
            'announce' => array('required'),
            'body'     => array('required'),
        );

    /**
     * Initializing table item data.
     *
     * @param AMI_ModTable $oTable  Module table model
     * @param DB_Query     $oQuery  Required for load or save operations
     */
    public function __construct(AMI_ModTable $oTable, DB_Query $oQuery = null){
        parent::__construct($oTable, $oQuery);

        $this->oTable->addValidators($this->aCommonFieldsValidators);
        $this->setFieldCallback('header', array($this, 'fcbHTMLEntities'));
    }

    /**
     * Returns link for current module & specific lang& pageID.
     *
     * @param  string $locale  Locale
     * @param  int $pageId  Page id (used for multipage modules)
     * @return string|boolean
     */
    // WTF?
    /*
    public function getModLink($locale = 'en', $pageId = 0){
        return AMI_PageManager::getModLink($this->oTable->getSubItemsModId(), $locale, $pageId);
    }
    */
}

/**
 * Categories module table list model.
 *
 * @package    ModuleComponent
 * @subpackage Model
 * @since      5.14.4
 */
class AMI_CatModTableList extends AMI_ModTableList{
}
