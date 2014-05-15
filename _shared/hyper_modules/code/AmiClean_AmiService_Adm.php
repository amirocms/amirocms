<?php
/**
 * AmiClean/AmiService configuration admin action controller.
 *
 * @package    Config_AmiClean_AmiService
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_AmiService_Adm extends Hyper_AmiClean_Adm{
    /**
     * Constructor.
     *
     * @param AMI_Request  $oRequest   Request object
     * @param AMI_Response $oResponse  Response object
     */
    public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){
        global $Core;

        // System user access only
        /*
		if(is_object($Core) && $Core instanceof CMS_Core){
            $isSysUser = $Core->isSysUser();
        }else{
            $isSysUser = AMI::getSingleton('core')->isSysUser();
        }
        if(!$isSysUser){
            return;
        }
		*/

        parent::__construct($oRequest, $oResponse);
        $this->addComponents(array('form'));
    }
}

/**
 * AmiClean/AmiService configuration model.
 *
 * @package    Config_AmiClean_AmiService
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_AmiService_State extends Hyper_AmiClean_State{
}

/**
 * AmiClean/AmiService configuration form component controller.
 *
 * @package    Config_AmiClean_AmiService
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_AmiService_FormAdm extends Hyper_AmiClean_FormAdm{
    /**
     * Specifies whether component has a model or not
     *
     * @var bool
     */
    protected $useModel = FALSE;

    /**
     * Initialization.
     *
     * @return $this
     */
    public function init(){
        return parent::init();
    }

    /**#@-*/
    /**
     * Save action handler.
     *
     * @param  array &$aEvent  Event data
     * @return array
     */
    public function _save(array &$aEvent){
        $this->displayView();
        $oResponse = AMI::getSingleton('response');
        $oRequest = AMI::getSingleton('env/request');
        require_once($GLOBALS["FUNC_INCLUDES_PATH"]."func_file_system.php");

        $oDB = AMI::getSingleton('db');
        $sAction = $oRequest->get('service_action', null);
        switch($sAction){
            case 'delete_generated':
                $message = 'status_generated_deleted';
                $generatedRootPath = AMI_Registry::get('path/root').AMI_Registry::get('CUSTOM_PICTURES_HTTP_PATH');
                $list = getDirFileList($generatedRootPath, '*', false, true);
                foreach($list as $dir){
                    $curGeneratedDir = $generatedRootPath.$dir.'/generated/';
                    if(is_dir($curGeneratedDir)){
                        $generatedDir = dir($curGeneratedDir);
                        if(is_object($generatedDir)){
                            while(false !== ($entry = $generatedDir->read())){
                                if(is_file($curGeneratedDir.$entry) && ($entry != "." && $entry != "..")){
                                    @unlink($curGeneratedDir.$entry);
                                }
                            }
                        }
                    }
                }
                break;
            case 'cache_truncate':
                $message = 'status_cache_truncated';
                $oDB->query(DB_Query::getSnippet("TRUNCATE cms_cache"));
                $oDB->query(DB_Query::getSnippet("TRUNCATE cms_cache_content"));
                $oDB->query(DB_Query::getSnippet("TRUNCATE cms_cache_blocks"));
                break;
        }

        $oRequest->set('id', 0);
        $this->oItem = null;
        $oResponse->addStatusMessage($message);
        AMI_Event::fire('dispatch_mod_action_form_edit', $aEvent, $this->getModId());
        return $aEvent;
    }
}

/**
 * AmiClean/AmiService configuration form component view.
 *
 * @package    Config_AmiClean_AmiService
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_AmiService_FormViewAdm extends Hyper_AmiClean_FormViewAdm{
    /**
     * Form view placeholders
     *
     * @var array
     */
    protected $aPlaceholders = array('#form', '#first', 'first', '#tabset', 'tabset', 'form');

    /**
     * Initialization.
     *
     * @return AMI_View
     */
    public function init(){
        global $db;
		require_once($GLOBALS["FUNC_INCLUDES_PATH"]."func_file_system.php");

        // get id
        $oRequest = AMI::getSingleton('env/request');
        $itemId = $oRequest->get('id', null);

        // time info
        $serverTimezone = date_default_timezone_get();
        date_default_timezone_set('Etc/GMT');
        $curTime = date(AMI::getDateFormat(AMI_Registry::get('lang', 'en'), AMI_Lib_Date::FMT_BOTH_ZONE));
        date_default_timezone_set($serverTimezone);
        $serverTime = date(AMI::getDateFormat(AMI_Registry::get('lang', 'en'), AMI_Lib_Date::FMT_BOTH_ZONE));
        $this
            ->addField(array('name' => 'time', 'value' => '', 'position' => 'first.after'))
            ->addField(array('name' => 'server_time', 'value' => $serverTime));

        // make tabs
        $this->addTabContainer('tabset', 'server_time.after');
		$this->addTab('cache', 'tabset', self::TAB_STATE_ACTIVE, 'tabset.end');
        $this->addTab('service', 'tabset', self::TAB_STATE_COMMON, 'tabset.end');
        // $this->addTab('gr_op', 'tabset', self::TAB_STATE_COMMON, 'tabset.end');

        // service tab
        $oUser = AMI::getSingleton('env/session')->getUserData();
        $aCookiesData = AMI::getSingleton('db')->fetchValue(DB_Query::getSnippet("SELECT data FROM cms_cookies WHERE id_member = %s")->plain($GLOBALS['_h']['uid']));
        //d::pr($aCookiesData);
        $cookiesDataSize = mb_strlen($aCookiesData, '8bit');
        $cookiesDataSize = number_format($cookiesDataSize/1024, 2, '.', '') . " Kb";

        $generatedFilesSize = 0;
        $generatedRootPath = AMI_Registry::get('path/root').AMI_Registry::get('CUSTOM_PICTURES_HTTP_PATH');
        $list = getDirFileList($generatedRootPath, '*', false, true);
        foreach($list as $dir){
            $curGeneratedDir = $generatedRootPath.$dir.'/generated/';
            if(is_dir($curGeneratedDir)){
                $generatedDir = dir($curGeneratedDir);
                if(is_object($generatedDir)){
                    while(false !== ($entry = $generatedDir->read())){
                        if(is_file($curGeneratedDir.$entry) && ($entry != "." && $entry != "..")){
                            $generatedFilesSize += filesize($curGeneratedDir.$entry);
                        }
                    }
                }
            }
        }
        if($generatedFilesSize/1024 < 1024){
                $generatedFilesSize = number_format($generatedFilesSize/1024, 2, '.', '') . " Kb";
        }else{
                $generatedFilesSize = number_format($generatedFilesSize/1048576, 2, '.', '') . " Mb";
        }

        $this
            ->addField(array('name' => 'service_action', 'type' => 'hidden', 'value' => ''))
            ->addField(array('name' => 'drop_user_cookie_data', 'value' => $cookiesDataSize, 'position' => 'service.end'))
            ->addField(array('name' => 'delete_generated', 'value' => $generatedFilesSize, 'position' => 'service.end'))
            ->addField(array('name' => 'options_data', 'value' => '', 'position' => 'service.end'));

        // cache tab
        $oCore = $GLOBALS['Core'];
        $aInfo = $db->GetTableInfo("cms_cache_content");
        $vRows = $aInfo["rows"];
        $vAvgSize = $aInfo["avg_row_size"]/1048576;
        $vSize = number_format(($vAvgSize*$aInfo["rows"]), 2, '.', '');
        $vAvgSize = number_format($vAvgSize*1024, 2, '.', '');
        $limit = 0;
        $storageLimit = $oCore->Cache->StorageLimit;
        if($storageLimit){
            $limit = $storageLimit;
        }elseif(isset($GLOBALS['CONNECT_OPTIONS']['cache_storage_size'])){
            $limit = $GLOBALS['CONNECT_OPTIONS']['cache_storage_size'];
        }

        $expiredPages = AMI::getSingleton('db')->fetchValue(DB_Query::getSnippet('SELECT count(id) as expired FROM cms_cache WHERE date_expire < NOW()'));

        $cacheSize = ($vSize > 0 ? ' ~ ' : '') . $vSize . " Mb " . ($storageLimit > 0 ? (" (" . number_format($vSize/$storageLimit*100, 2, '.', '') . "%)" ) : "") . ', ' . $this->aLocale['cache_max_size'] . ":";
        $cachePages = $vRows . ", " . $this->aLocale['cache_expired'] . ": " . (int)$expiredPages . " (" .
            ($vRows > 0 ? number_format($expiredPages / $vRows * 100, 2, '.', '') : 0) . "%)";
        $cacheSizeInfo = $vAvgSize . " Kb";

        $this
            ->addField(array('name' => 'cache_size', 'value' => $cacheSize, 'position' => 'cache.end'))
            ->addField(array('name' => 'cache_size_link', 'value' => $storageLimit, 'position' => 'cache.end'))
            ->addField(array('name' => 'cache_pages', 'value' => $cachePages, 'position' => 'cache.end'))
            ->addField(array('name' => 'cache_size_info', 'value' => $cacheSizeInfo, 'position' => 'cache.end'));

        // get cache L3 info
        $aInfo = $db->GetTableInfo("cms_cache_blocks");
        $vRows = $aInfo["rows"];
        $vAvgSize = $aInfo["avg_row_size"]/1048576;
        $vSize = number_format(($vAvgSize*$aInfo["rows"]), 2, '.', '');
        $vAvgSize = number_format($vAvgSize*1024, 2, '.', '');

        $expiredBlocks = AMI::getSingleton('db')->fetchValue(DB_Query::getSnippet('SELECT count(id) as expired FROM cms_cache_blocks WHERE date_expire < NOW()'));

        $cacheL3Size = ($vSize > 0 ? ' ~ ' : '') . $vSize . " Mb " . ($limit > 0 ? ("(".(number_format($vSize/$limit*100, 2, '.', ''))."%)") : "");
        $cacheL3Pages = $vRows . ", " . $this->aLocale['cache_expired'] . ": " . $expiredBlocks . " (" .
            ($vRows > 0 ? number_format($expiredBlocks / $vRows * 100, 2, '.', '') : 0) . "%)";
        $cacheL3SizeInfo = $vAvgSize . " Kb";

        $this
            ->addField(array('name' => 'cache_l3_size', 'value' => $cacheL3Size, 'position' => 'cache.end'))
            ->addField(array('name' => 'cache_l3_pages', 'value' => $cacheL3Pages, 'position' => 'cache.end'))
            ->addField(array('name' => 'cache_l3_size_info', 'value' => $cacheL3SizeInfo, 'position' => 'cache.end'))
            ->addField(array('name' => 'cache_truncate', 'position' => 'cache.end'));

        $this->addField(
            array(
            'name'  => 'mod_action',
            'value' => 'form_save',
            'type'  => 'hidden'
            )
        );

		$this->addScriptCode($this->parse('javascript', $aScope = array('date_format' => AMI::getDateFormat(AMI_Registry::get('lang', 'en'), AMI_Lib_Date::FMT_BOTH_ZONE))));
        $this->addScriptFile('_admin/skins/vanilla/_js/ami_service.js');

        return parent::init();
    }
}
