<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Environment
 * @version   $Id: AMI_GlobalFilters.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Global filters.
 *
 * @package Environment
 * @static
 * @since   x.x.x
 * @amidev  Temporary
 */
final class AMI_GlobalFilters{
    /**
     * Set global filter by its type.
     *
     * @param  string $filterType  Filter type
     * @return void
     */
    public static function setGlobalFilter($filterType){
        switch($filterType){
            case 'side':
                // Data locale filtering
                $side = AMI_Registry::get('side');
                AMI_Event::addHandler(
                    'on_query_add_table',
                    array('AMI_GlobalFilters', 'handleFilterLangData'),
                    AMI_Event::MOD_ANY
                );
                $bMultiSite = AMI::getSingleton('env/cookie')->get('multiSite/enabled', FALSE);
                if(!$bMultiSite){
                    // `id_site` filtering
                    AMI_Event::addHandler(
                        'on_query_add_table',
                        array('AMI_GlobalFilters', 'handleFilterSiteId'),
                        AMI_Event::MOD_ANY
                    );
                }
                if($side == 'frn'){
                    // Publicity filtering
                    AMI_Event::addHandler(
                        'on_query_add_table',
                        array('AMI_GlobalFilters', 'handleFilterPublic'),
                        AMI_Event::MOD_ANY
                    );
                    if(AMI_Registry::exists('page/modId')){
                        $modId = AMI_Registry::get('page/modId');
                        if(
                            AMI::issetAndTrueOption('core', 'multi_page_allowed') &&
                            AMI::issetAndTrueOption($modId, 'multi_page')
                        ){
                            // `id_page` filtering
                            AMI_Event::addHandler(
                                'on_query_add_table',
                                array('AMI_GlobalFilters', 'handleFilterIdPage'),
                                AMI_Event::MOD_ANY
                            );
                        }
                        if(AMI::issetAndTrueOption($modId, 'hide_future_items')){
                            // Hiding elements having date in the future
                            AMI_Event::addHandler(
                                'on_query_add_table',
                                array('AMI_GlobalFilters', 'handleFilterDate'),
                                AMI_Event::MOD_ANY
                            );
                        }
                    }
                }
                break;
            default:
                $aEvent = array();
                /**
                 * Called when a global filter type {FilterType}.
                 *
                 * @event set_global_filter_{FilterType} AMI_Event::MOD_ANY
                 */
                AMI_Event::fire('set_global_filter_' . $filterType, $aEvent, AMI_Event::MOD_ANY);
                break;
        }
    }

    /**
     * Set global filter by its type.
     *
     * @param  AMI_ModTable $oTable     Filter type
     * @param  string       $alias      Alias
     * @param  DB_Query     $oQuery     Query object
     * @param  string       $fieldName  Field name
     * @param  string       $value      Field value
     * @return void
     */
    protected static function setFilter(AMI_ModTable $oTable, $alias, DB_Query $oQuery, $fieldName, $value){
        if($oTable->hasField($fieldName)){
            if($alias){
                $alias .= '.';
            }
            $oQuery->addWhereDef(
                DB_Query::getSnippet("AND %s`%s` = %s")
                ->plain($alias)
                ->plain($fieldName)
                ->q($value)
            );
        }
    }

    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */

    /**
     * Adds filter by lang data.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public static function handleFilterLangData($name, array $aEvent, $handlerModId, $srcModId){
        self::setFilter($aEvent['oTable'], $aEvent['alias'], $aEvent['oQuery'], 'lang', AMI_Registry::get('lang_data'));
        return $aEvent;
    }

    /**
     * Adds filter by site id.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public static function handleFilterSiteId($name, array $aEvent, $handlerModId, $srcModId){
        // Do not add filter if multisite support disabled for module by option
        $modId = AMI_Registry::get('modId', false);
        if(!$modId){
            return $aEvent;
        }
        if(AMI::issetOption($modId, 'multi_site') && !AMI::getOption($modId, 'multi_site')){
            return $aEvent;
        }
        self::setFilter($aEvent['oTable'], $aEvent['alias'], $aEvent['oQuery'], 'site_id', AMI::getSingleton('env/cookie')->get('multiSite/siteId', 0));
        return $aEvent;
    }

    /**
     * Adds filter by `public` status.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public static function handleFilterPublic($name, array $aEvent, $handlerModId, $srcModId){
        self::setFilter($aEvent['oTable'], $aEvent['alias'], $aEvent['oQuery'], 'public', '1');
        return $aEvent;
    }

    /**
     * Adds filter by `id_page`.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public static function handleFilterIdPage($name, array $aEvent, $handlerModId, $srcModId){
        $modId = $aEvent['modId'];
        $currentPageId = (int)AMI_Registry::get('page/id');
        $aPageIds = array_unique(array($currentPageId, 0));
        $useCondition = $aEvent['oTable']->hasField('id_page');
        $alias = $aEvent['alias'];
        if($useCondition){
            $noCatModId = preg_replace('/_cat$/', '', $modId);
            if(AMI_Registry::get('AMI/Module/Environment/Filter/skipIdPage', FALSE)){
                $useCondition = FALSE;
            }elseif(AMI_Registry::get('AMI/Module/Environment/Filter/active', FALSE)){
                $aForceIdPage = (array)AMI::getSingleton('env/request')->get('force_id_page', array());
                if(sizeof($aForceIdPage) && !in_array(-1, $aForceIdPage) && !(sizeof($aForceIdPage) == 1 && empty($aForceIdPage[0]))){
                    $aPageIds = $aForceIdPage;
                }else{
                    $useCondition = FALSE;
                }
            }else{
                $useCatIdPages =
                    AMI::issetAndTrueOption($noCatModId, 'use_categories') &&
                    AMI::issetOption($noCatModId, 'mod_cat_id_pages');
                $useIdPages = !$useCatIdPages && AMI::issetOption($modId, 'mod_id_pages');
                if($useCatIdPages || $useIdPages){
                    if($useCatIdPages && $modId === $noCatModId){
                        $alias = 'cat';
                    }
                    $aPageIds =
                        $useCatIdPages
                            ? AMI::getOption($noCatModId, 'mod_cat_id_pages')
                            : AMI::getOption($modId, 'mod_id_pages');

                    if(in_array(-1, $aPageIds)){
                        $aPageIds = array();
                    }
                    $useCondition = sizeof($aPageIds) > 0;
                    if($useCondition){
                        $index = array_search(-2, $aPageIds);
                        if($index !== FALSE){
                            $aPageIds[$index] = $currentPageId;
                            $aPageIds = array_unique($aPageIds);
                        }
                    }
                }
            }
        }
        if($useCondition){
            $aEvent['oQuery']->addWhereDef(
                DB_Query::getSnippet("AND (" . $alias . ".`id_page` IN (%s))")
                ->implode($aPageIds)
            );
        }
        return $aEvent;
    }

    /**
     * Adds filter by date.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public static function handleFilterDate($name, array $aEvent, $handlerModId, $srcModId){
        if($aEvent['oTable']->hasField('date_created')){
            $aEvent['oQuery']->addWhereDef(
                DB_Query::getSnippet("AND `" . $aEvent['alias'] . "`.`" . $aEvent['oTable']->getFieldName('date_created') . "` <= NOW()")
            );
        }
        return $aEvent;
    }

    /**#@-*/
}
