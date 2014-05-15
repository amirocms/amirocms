<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Module_ModManager
 * @version   $Id: ModManager_Table.php 46891 2014-01-22 12:37:00Z Leontiev Anton $
 * @amidev
 */

/**
 * Module Manager module table model.
 *
 * See {@link AMI_ArrayIterator::getAvailableFields()} for common fields description.
 *
 * @package    Module_ModManager
 * @subpackage Model
 * @resource   mod_manager/table/model <code>AMI::getResourceModel('mod_manager/table')</code>
 * @amidev
 */
class ModManager_Table extends AMI_ArrayIterator{
    /**
     * Table fields and field types as result of DESCRIBE
     *
     * @var array
     */
    protected $aFields = array(
        'id',
        'date_installed',
        'order',
        'section',
        'taborder',
        'sort_taborder',
        'hypermod',
        'hypermod_caption',
        'config',
        'config_caption',
        'section_caption',
        'caption',
        'meta',
        'is_sys',
        'distrib_caption'
    );

    /**
     * Returns item model object and load data for primary key field param.
     *
     * @param  mixed $id       Primary key value
     * @param  array $aFields  Fields to load
     * @return ModManager_TableItem
     * @see    ModManager_TableItem::addFields() for $aFields parameter explanation
     */
    public function find($id, array $aFields = array()){
        $aConditions = array('id' => $id);
        $oItem = $this->findByFields($aConditions, $aFields);
        if($oItem){
            $oItem->addSearchCondition($aConditions);
            $oItem->load();
        }
        return $oItem;
    }
}

/**
 * Module Manager module table list model.
 *
 * Full environment is requred.
 *
 * @package    Module_ModManager
 * @subpackage Model
 * @resource   mod_manager/table/model/list <code>AMI::getResourceModel('mod_manager/table')->getList()</code>
 * @amidev
 */
class ModManager_TableList extends AMI_ArrayIteratorList{
    /**
     * Loads registered modules list and init recordset.
     *
     * @return ModManager_TableItem
     */
    public function load(){
        $aEvent = array(
            'modId' => $this->getModId(),
            'oList' => $this,
        );
        /**
         * -----------------
         *
         * @event      on_list_recordset $modId
         * @eventparam string modId  Module id
         * @eventparam ModManager_TableList oList  Table list object
         */
        AMI_Event::fire('on_list_recordset', $aEvent, $this->getModId());

        $oDeclarator = AMI_ModDeclarator::getInstance();
        $aInstalled = $oDeclarator->getRegistered();
        if(sizeof($aInstalled)){
            /**
             * @var AMI_TemplateSystem
             */
            $oTpl = AMI::getSingleton('env/template_sys');
            $aLocale = $oTpl->parseLocale('templates/lang/_menu_all.lng');
            $aHeaderLocale = $oTpl->parseLocale('templates/lang/_headers.lng');
            $aSectionLocale = $oTpl->parseLocale('templates/lang/_menu_owners.lng');
        }
        $this->aRaw = array();
        AMI_Service::setAutoloadWarning(FALSE);
        $locale = AMI_Registry::get('lang');
        $uninstalledModId = AMI_Registry::get('mod_manager_uninstalled_mod_id', FALSE);
        $aDateInstalled = array();
        if(sizeof($aInstalled)){
            $oQuery =
                DB_Query::getSnippet(
                    "SELECT `module_name`, `date_created` " .
                    "FROM `cms_options` " .
                    "WHERE `module_name` IN (%s) AND `name` = %s"
                )
                ->implode($aInstalled)
                ->q('options_dump');
            $oDB = AMI::getSingleton('db');
            $rs = $oDB->select($oQuery);
            foreach($rs as $aRecord){
                if($aRecord['date_created'] != '0000-00-00 00:00:00'){
                    $aDateInstalled[$aRecord['module_name']] = $aRecord['date_created'];
                }
            }
            unset($rs, $oDB);
        }
        $aPkgInfos = array();
        $aInstallIds = array();
        foreach($aInstalled as $modId){
            if($modId === $uninstalledModId){
                // Don't display just uninstalled module
                continue;
            }
            if(!$GLOBALS['Core']->isInstalled($modId)){
                // Don't display modules with setInstalled = false
                continue;
            }
            $installId = (int)$oDeclarator->getAttr($modId, 'id_install');
            if($installId > 0 && in_array($installId, $aInstallIds)){
                // skip non-system modles having same inslallId
                continue;
            }
            $aInstallIds[] = $installId;
            $pkgId = $oDeclarator->getAttr($modId, 'id_pkg');
            if(!isset($aPkgInfos[$pkgId])){
                $aPkgInfo = AMI_PackageManager::getInstance()->getManifest($pkgId);
                $aPkgInfos[$pkgId] = $aPkgInfo;
                unset($aPkgInfo);
            }

            list($hypermod, $config) = $oDeclarator->getHyperData($modId);
            $section = $oDeclarator->getSection($modId);
            if(is_null($oDeclarator->getParent($modId))){
                // No parent module, correct instance
                $path = AMI_Registry::get('path/hyper_shared') . "configs/{$hypermod}";
                $sectionCaption = str_replace(' ', '&nbsp;', $aSectionLocale[$section]);
                $tabOrder = AMI::issetProperty($modId, 'taborder') ? AMI::getProperty($modId, 'taborder') : '';
                $sortTaborder = $sectionCaption . str_pad($tabOrder, 10, '0', STR_PAD_LEFT);
                if(isset($aHeaderLocale[$modId])){
                    $caption = $aHeaderLocale[$modId];
                    if('modules' === $section){
                        $pos = mb_strpos($caption, ':');
                        if($pos !== FALSE){
                            $caption = mb_substr($caption, 0, $pos - 1);
                        }
                    }
                }elseif($aLocale[$modId]){
                    $caption = $aLocale[$modId];
                }else{
                    $caption = $modId;
                }
                $caption = mb_convert_case($caption, MB_CASE_TITLE);
                $aRow = array(
                    'id'               => $modId,
                    'date_installed'   => isset($aDateInstalled[$modId]) ? $aDateInstalled[$modId] : '',
                    'section'          => $section,
                    'taborder'         => $tabOrder,
                    'sort_taborder'    => $sortTaborder,
                    'section_caption'  => $sectionCaption,
                    'hypermod'         => $hypermod,
                    'hypermod_caption' => '',
                    'config'           => $config,
                    'config_caption'   => '',
                    'caption'          => $caption,
                    'is_sys'           => is_dir($path) && is_dir($path . '/' . $config),
                    'distrib_id'       => $pkgId,
                    'distrib_caption'  =>
                        $aPkgInfos[$pkgId]
                        ? $aPkgInfos[$pkgId]['information'][$locale]['title'] :
                        '{unknown}'
                );
                if($installId){
                    // Common package, instances for meta has same 'id_install' attribute
                    $aInstanceIds = $oDeclarator->getModIdsByInstallId($installId);
                }else{
                    // Amiro base package, instances for meta are children of current module
                    $aInstanceIds = $oDeclarator->getSubmodules($modId);
                    array_unshift($aInstanceIds, $modId);
                }
                $aRow['meta'] = $aPkgInfos[$pkgId]['information'][$locale];
                $aRow['meta']['instances'] = array();
                foreach($aInstanceIds as $index => $instanceId){
                    list($hypermod, $config) = $oDeclarator->getHyperData($instanceId);
                    /*
                    $aRow['meta'] = array(
                        'caption'      => $aRow['caption'],
                        'hyper_config' => '',
                        'section'      => $sectionCaption,
                        'id'           => $modId
                    );
                    */
                    $section = $oDeclarator->getSection($instanceId);
                    $aCaption = AMI_Service_Adm::getModulesCaptions(array($instanceId), TRUE, array($section), TRUE);
                    $aMeta = array(
                        // 'instance_title' => $aCaption[$instanceId] // isset($aLocale[$instanceId]) ? $aLocale[$instanceId] : '{' . $instanceId . '}'
                        'instance_title' => isset($aCaption[$instanceId]) ? $aCaption[$instanceId] : '{' . $instanceId . '}'
                    );
                    foreach(
                        array(
                            'hypermod_caption' => 'Hyper_' . AMI::getClassPrefix($hypermod) . '_Meta',
                            'config_caption'   => AMI::getClassPrefix($hypermod) . '_' . AMI::getClassPrefix($config) . '_Meta',
                        ) as $key => $metaClassName
                    ){
                        if(class_exists($metaClassName)){
                            /**
                             * @var AMI_Hyper_Meta
                             */
                            $oMeta = new $metaClassName;
                            /*
                            if($key === 'config_caption' && $oMeta->isPermanent()){
                                continue 2;
                            }
                            */
                            if(!$oMeta->isVisible()){
                                continue 2;
                            }
                            $title = $oMeta->getTitle($locale);
                            if(!$index){
                                $aRow[$key] = $title;
                                $aRow['meta']['modes'] = array_keys($oMeta->getAllowedModes('uninstall'));
                            }
                            $aMeta[$key] = $title;
                        }
                    }
                    $aHyperInfo = $oMeta->getInfo($locale);
                    if(is_array($aHyperInfo)){
                        $aMeta += $aHyperInfo;
                        // $aRow['meta'] += $aMeta;
                        if(isset($aMeta['hypermod_caption'])){
                            $aMeta['hyper_config'] = $aMeta['hypermod_caption'];
                            unset($aMeta['hypermod_caption']);
                            if(isset($aMeta['config_caption'])){
                                $aMeta['hyper_config'] .= ' / ' . $aMeta['config_caption'];
                                unset($aMeta['config_caption']);
                            }
                        }
                        $aRow['meta']['instances'][$instanceId] = $aMeta;
                    }
                }
                $aRow['order'] =
                    ($aRow['date_installed'] !== '' ? '1_' . $aRow['date_installed'] : '0_0000-00-00 00:00:00') . '_' .
                    (isset($aRow['section_caption']) ? $aRow['section_caption'] : $aRow['section']) . ' ' .
                    (isset($aRow['hypermod_caption']) ? $aRow['hypermod_caption'] : $aRow['hypermod']) . ' ' .
                    (isset($aRow['config_caption']) ? $aRow['config_caption'] : $aRow['config']) . ' ' .
                    $aRow['caption'] . ' ' . $modId;
                $this->aRaw[] = $aRow;
                // d::vd1($aRow['meta'], 'meta');###
            }
        }
        AMI_Service::setAutoloadWarning(FALSE);

        $path = AMI_Registry::get('path/hyper_local') . 'declaration/pseudo.php';
        $oStorage = new AMI_Storage_FS;
        $aRecords =
            $oStorage->exists($path)
            ? require($path)
            : array();
        foreach($aRecords as $aRecord){
            $installId = $aRecord['installId'];
            $hypermod  = $aRecord['pkgInfo']['install'][0]['hypermodule'];
            $config    = $aRecord['pkgInfo']['install'][0]['configuration'];

            if(isset($aRecord['isPermanent'])){
                // #CMS-11420: Special case for pseudo instances having no meta files and no config folder
                $aInfo = $aRecord['pkgInfo']['information'][$locale];
                $aMeta = array(
                    'description'    => $aInfo['description'],
                    'author'         => '<a href="' . $aInfo['source'] . '" target="_blank">' . $aInfo['author'] . '</a>',
                    'modes'          => array()
                );
            }else{
                $oMeta = AMI_Package::getMeta($hypermod, $config);
                if(!$oMeta){
                    trigger_error(
                        "Hyper/config: '" .
                        $aRecord['pkgInfo']['install'][0]['hypermodule'] . '/' .
                        $aRecord['pkgInfo']['install'][0]['configuration'] . ' meta file not found',
                        E_USER_WARNING
                    );
                    continue; // foreach($aRecords as $aRecord){
                }
                $aMeta =
                    $oMeta->getInfo($locale) +
                    array(
                        'modes' => array_keys($oMeta->getAllowedModes('uninstall'))
                    );
                $aInfo['title'] = $aMeta['description'];
                unset($oMeta);
            }

            /*
            if(!is_object($oMeta)){
                echo d::getTraceAsString();
                var_dump($aRecord['pkgInfo']['install'][0]);die;###
            }
            */
            $aRow = array(
                'id'               => 'pseudo_' . $installId,
                'date_installed'   => $aRecord['date'],
                'section'          => '',
                'taborder'         => '',
                'sort_taborder'    => '',
                'section_caption'  => '',
                'hypermod'         => '',
                'hypermod_caption' => '',
                'config'           => '',
                'config_caption'   => '',
                'caption'          => $aInfo['title'],
                'is_sys'           => FALSE,
                'distrib_caption'  =>
                    empty($aRecord['pkgInfo']['information'][$locale]['title'])
                    ? $aRecord['pkgInfo']['id']
                    : $aRecord['pkgInfo']['information'][$locale]['title'],
                'meta'             => $aMeta,
                'order'            => '1_' . $aRecord['date'] . '_' . $installId
            );
            if('_' === $installId[0]){
                $aRow['order'] =
                    '0_' .
                    ' ' .
                    /*
                    (isset($aRow['section_caption']) ? $aRow['section_caption'] : $aRow['section']) . ' ' .
                    (isset($aRow['hypermod_caption']) ? $aRow['hypermod_caption'] : $aRow['hypermod']) . ' ' .
                    (isset($aRow['config_caption']) ? $aRow['config_caption'] : $aRow['config']) . ' ' .
                    */
                    $aRow['caption'] . ' ' . $installId;
            }

            $this->aRaw[] = $aRow;
        }

        $sortField = $this->getSortField() == 'taborder' ? 'sort_taborder' : $this->getSortField();
        $sortDirection = $this->sortDirection;
        $this->sortDirection = SORT_ASC;
        $this->sortList('order');
        foreach(array_keys($this->aRaw) as $index){
            $this->aRaw[$index]['order'] = $index + 1;
        }
        $this->sortDirection = $sortDirection;
        $this->filterByConditions();
        $this->total = sizeof($this->aRaw);
        $this->sortList($sortField, $sortField != 'order' ? null : SORT_NUMERIC);
        $this->storeKeys('id');
        $this->loadCurrentPage();
        $this->seek($this->start);
        $this->aRaw = FALSE;
        return $this;
    }
}

/**
 * Module Manager module table item model.
 *
 * @package    Module_ModManager
 * @subpackage Model
 * @resource   mod_manager/table/model/item <code>AMI::getResourceModel('mod_manager/table')->getItem()</code>
 * @amidev
 */
class ModManager_TableItem extends AMI_ArrayIteratorItem{

    /**
     * Returns primary key field name.
     *
     * @return string
     */
    public function getPrimaryKeyField(){
        return 'order';
    }

    /**
     * Loads data.
     *
     * @return ModManager_TableItem
     */
    public function load(){
        if(!empty($this->aCondition['id'])){
            // Fill data for seacrched instance
            $modId = $this->aCondition['id'];
            $this->id = $modId;
            $this->aData['id'] = $modId;
            $oDeclarator = AMI_ModDeclarator::getInstance();
            $aHyperData = $oDeclarator->getHyperData($modId);
            $this->aData['caption'] = AMI::getOption($modId, 'admin_menu_caption');
            $this->aData['section'] = $oDeclarator->getSection($modId);
            $this->aData['hyper_config'] = $aHyperData[0] . '/' . $aHyperData[1];
        }
        return $this;
    }
}
