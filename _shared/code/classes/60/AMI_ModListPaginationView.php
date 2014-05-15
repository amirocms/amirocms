<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModListPaginationView.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Module list pagination view.
 *
 * @package    ModuleComponent
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
class AMI_ModListPaginationView extends AMI_View{
    /**
     * Template file name
     *
     * @var string
     */
    protected $tplFileName = 'templates/modules/_pagination.tpl';

    /**
     * Template block name
     *
     * @var string
     */
    protected $tplBlockName = 'pagination';
    /**
     * Locale file name
     *
     * @var string
     */
    protected $localeFileName = 'templates/lang/modules/_pagination.lng';

    /**#@+
     * Pagination set names
     */

    /**
     * First
     *
     * @var string
     */
    protected $firstItemSet   = 'first';

    /**
     * Last
     *
     * @var string
     */
    protected $lastItemSet    = 'last';

    /**
     * Prev
     *
     * @var string
     */
    protected $prevItemSet    = 'prev';

    /**
     * Next
     *
     * @var string
     */
    protected $nextItemSet    = 'next';

    /**
     * Active
     *
     * @var string
     */
    protected $activeItemSet  = 'active';

    /**
     * Page
     *
     * @var string
     */
    protected $pageItemSet    = 'page';

    /**
     * Spacer
     *
     * @var string
     */
    protected $spacerItemSet  = 'spacer';

    /**
     * Page size row
     *
     * @var string
     */
    protected $pageSizeRowSet = 'page_size_row';

    /**
     * Page size
     *
     * @var string
     */
    protected $pageSizeSet    = 'page_size';

    /**
     * Pagination
     *
     * @var string
     */
    protected $paginationSet  = 'pagination';

    /**#@-*/

    /**
     * Sets model.
     *
     * @param  array $oModel  Model
     * @return this
     */
    protected function _setModel(array $oModel){
        parent::_setModel($oModel);
        // Add list actions handling because we know the module id now
        AMI_Event::addHandler('on_before_view_list', array($this, AMI::actionToHandler('before_view')), $this->getModId());
        AMI_Event::addHandler('on_list_view', array($this, AMI::actionToHandler('view')), $this->getModId());
        AMI_Event::addHandler('on_list_recordset_loaded', array($this, 'handleListRecordsetLoaded'), $this->getModId());
        return $this;
    }

    /**
     * AMI_View::get() implementation.
     *
     * @return string
     */
    public function get(){
        $oTpl = $this->getTemplate();
        $aScope = $this->getScope('pagination');

        // Show pages links
        // TODO. Page listing simplified a lot
        $pagesList = '';
        $pagesCount = $this->getPagesCount();
        $activePage = $this->getActivePage();
        for($i = 0; $i < $pagesCount; $i++){
            $aPageData = array(
                'page' => $i + 1,
                'offset' => $i * $this->oModel['page_size']
            );
            $pagesList .= $oTpl->parse($this->tplBlockName . ':' . ($i == $activePage? $this->activeItemSet: $this->pageItemSet), $aPageData);
            $pagesList .= $oTpl->parse($this->tplBlockName . ':' . $this->spacerItemSet, $aPageData);
        }
        $aScope['body'] = $pagesList;

        // Draw page size box
        $pageOptions = '';
        foreach($this->oModel['page_size_options'] as $pageSize){
            $pageOptions .= $oTpl->parse(
                $this->tplBlockName . ':' . $this->pageSizeRowSet,
                $aScope + array(
                    'active'  => $pageSize == $this->oModel['page_size']? 'selected': '',
                    'value'   => $pageSize,
                    'caption' => $pageSize
                )
            );
        }
        $aScope['page_size'] = $oTpl->parse($this->tplBlockName . ':' . $this->pageSizeSet, $aScope + array('data' => $pageOptions));

        return $oTpl->parse($this->tplBlockName . ':' . $this->paginationSet, $aScope);
    }

    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */

    /**
     * Default list action (view).
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchBeforeView($name, array $aEvent, $handlerModId, $srcModId){
        // Raise up flag that we expect to get found rows count
        $oRequest = AMI::getSingleton('env/request');
        $aEvent['aScope']['calc_found_rows'] = $oRequest->get('calc_found_rows', false);
        return $aEvent;
    }

    /**
     * Dispatches view.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchView($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['aScope']['pagination1'] = $this->get();
        // $aEvent['aScope']['pagination2'] = $this->get();
        return $aEvent;
    }

    /**
     * Handles list recordset.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListRecordsetLoaded($name, array $aEvent, $handlerModId, $srcModId){
        // Get found rows count
        $this->oModel['max_count'] = $aEvent['numberOfFoundRows'];
        return $aEvent;
    }

    /**#@-*/

    /**
     * Returns module id.
     *
     * @return string
     */
    protected function getModId(){
        return $this->oModel['mod_id'];
    }

    /**
     * Returns start position.
     *
     * @return int
     */
    protected function getStartPos(){
        $pos = -1;
        if($this->oModel['start_offset'] <= 0){
            if($this->oModel['offset'] > 0){
                $pos = 0;
            }
            if($this->oModel['page_size'] > 0){
                $pos = $pos == -1 ? 0 : $this->oModel['offset'];
            }
        }else{
            // there are some records that we must skip
            $pos = max(0, $this->oModel['offset']) - $this->oModel['start_offset'];
        }
        return $pos + 1;
    }

    /**
     * Returns end position.
     *
     * @return int
     */
    protected function getEndPos(){
        return max(min($this->getStartPos() + $this->oModel['page_size'] - 1, $this->oModel['max_count']), 0);
    }

    /**
     * Returns pages count.
     *
     * @return int
     */
    protected function getPagesCount(){
        return
            $this->oModel['page_size'] > 0
            ? ceil($this->oModel['max_count'] / $this->oModel['page_size'])
            : 1;
    }

    /**
     * Returns active page number.
     *
     * @return int
     */
    protected function getActivePage(){
        return
            $this->oModel['page_size'] > 0
            ? (int)($this->oModel['offset'] / $this->oModel['page_size'])
            : 1;
    }
}
