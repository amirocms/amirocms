<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModItemsFrn.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     5.14.8
 */

/**
 * Module front items body type action controller.
 *
 * @package    ModuleComponent
 * @subpackage Controller
 * @since      5.14.8
 */
abstract class AMI_ModItemsFrn extends AMI_ModList{
    /**
     * Calculated cache expiration time
     *
     * @var int
     */
    protected static $cacheExpireTime;

    /**
     * Initialization.
     *
     * @return AMI_ModItemsFrn
     */
    public function init(){
        parent::init();
        $this->addSubComponent(AMI::getResource('list/pagination'));

        $this->bDispayView = true;
        $modId = $this->getModId();

        if(AMI_Registry::get('AMI/Module/Environment/Filter/active', false)){
            $this->addSubComponent(AMI::getResource($modId.'/filter/controller/frn'));
        }else{
            // No sticky items in filtered list view
            if(AMI::issetAndTrueProperty($modId, 'use_special_list_view')){
                $aShowAt = AMI::getOption($modId, 'show_urgent_elements');
                if(is_array($aShowAt) && (in_array('at_first_page', $aShowAt) || in_array('at_next_pages', $aShowAt))){
                    $this->addSubComponent(AMI::getResource($modId.'/sticky_items/controller/frn'));
                }
            }
        }

        return $this;
    }

    /**
     * Returns component type.
     *
     * @return string
     */
    public function getType(){
        return 'items';
    }

    /**
     * Returns component view.
     *
     * @return AMI_ViewEmpty
     */
    public function getView(){
        $oView = parent::getView();

        return $oView;
    }

    /**
     * Returns component expiration time.
     *
     * @return int
     */
    public function getCacheExpireTime(){
        if(!is_null(self::$cacheExpireTime)){
            return self::$cacheExpireTime;
        }
        $oModel = $this->getModel();
        $oDB = AMI::getSingleton('db');

        $result = null;
        $minimum = null;
        $table = $oModel->getTableName();
        $modId = $this->getModId();

        if(AMI::issetOption($modId, 'multi_site') && !AMI::getOption($modId, 'multi_site')){
            $siteId = 0;
        }else{
            $siteId = (int)AMI::getSingleton('env/cookie')->get('multiSite/enabled', 0);
        }
        $filter = ' AND lang = %s AND public = 1'.($siteId ? ' AND id_site = ' . $siteId : '');

        $date = $oModel->getFieldName('date_created');
        $sticky = $oModel->getFieldName('sticky');
        $stickyDate = $oModel->getFieldName('date_sticky_till');

        // get minimum expiration date for future items
        if(AMI::issetAndTrueOption($modId, 'hide_future_items')){
            $sql = 'SELECT MIN(%s) FROM %s WHERE %s > NOW() ' . $filter;

            $minimum = $oDB->fetchValue(
                DB_Query::getSnippet($sql)
                ->plain($date)
                ->plain($table)
                ->plain($date)
                ->q(AMI_Registry::get('lang_data'))
            );
            if($minimum){
                $minimum = strtotime($minimum);
            }
        }
        if(AMI::issetAndTrueProperty($modId, 'use_special_list_view')){
            // get expired sticky items and remove sticky flag
            $sql = 'SELECT 1 FROM %s WHERE %s = 1 AND %s < CONCAT(CURDATE(), %s) ' . $filter . ' LIMIT 1';

            $result = $oDB->fetchValue(
                DB_Query::getSnippet($sql)
                ->plain($table)
                ->plain($sticky)
                ->plain($stickyDate)
                ->q(' 23:59:59')
                ->q(AMI_Registry::get('lang_data'))
            );

            if($result){
                $oQuery = DB_Query::getSnippet('UPDATE %s SET %s = 0, %s = 0, %s = NULL WHERE %s < CONCAT(CURDATE(), %s) ' . $filter)
                    ->plain($table)
                    ->plain($sticky)
                    ->plain($oModel->getFieldName('hide_in_list'))
                    ->plain($stickyDate)
                    ->plain($stickyDate)
                    ->q(' 23:59:59')
                    ->q(AMI_Registry::get('lang_data'));
                $oDB->query($oQuery);
            }

            // get minimum expiration date for sticky items
            $sql = 'SELECT MIN(%s) FROM %s WHERE %s IS NOT NULL AND %s >= NOW() ' . $filter;
            $result = $oDB->fetchValue(
                DB_Query::getSnippet($sql)
                ->plain($stickyDate)
                ->plain($table)
                ->plain($stickyDate)
                ->plain($stickyDate)
                ->q(AMI_Registry::get('lang_data'))
            );

            // get minimum expiration date
            if($result){
                $result = strtotime(mb_substr($minimum, 0, 11) . '23:59:59');
            }
            if($minimum && $result){
                $result = min($minimum, $result);
            }elseif($minimum){
                $result = $minimum;
            }
        }
        self::$cacheExpireTime = $result;

        return $result;
    }
}
