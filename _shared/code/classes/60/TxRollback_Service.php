<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   TxService
 * @version   $Id: TxRollback_Service.php 50566 2014-05-13 07:13:19Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Module service functions.
 *
 * @package    TxService
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class TxRollback_Service extends AMI_Module_Service{
    /**
     * Request object
     *
     * @var AMI_Request
     */
    protected $oRequest;

    /**
     * Response object
     *
     * @var AMI_Response
     */
    protected $oResponse;

    /**
     * Array of supported locales
     *
     * @var array
     */
    protected $aSupportedLocales = array('en', 'ru');

    /**
     * Master password to authorize
     *
     * @var string
     */
    protected $masterPassword;

    /**
     * Cookie name
     *
     * @var string
     */
    protected $cookieName = 'ami_mp';

    /**
     * Cookie time to live, in seconds
     *
     * @var int
     */
    protected $cookieTTL = 3600; // 1 hour

    /**
     * Salt for store md5 password in cookie
     *
     * @var string
     */
    protected $salt = 'qweqwe';

    /**
     * Current CMS code version
     *
     * @var string
     */
    protected $version;

    /**
     * Template object
     *
     * @var AMI_TemplateSystem
     */
    protected $oTpl;

    /**
     * Dispatches front module action.
     *
     * @param  AMI_Request  $oRequest   Request
     * @param  AMI_Response $oResponse  Response
     * @return void
     */
    public function dispatchAction(AMI_Request $oRequest, AMI_Response $oResponse){
        global $CLASSES_PATH, $CONNECT_OPTIONS;

        ### {
        /*
        require_once $CLASSES_PATH . '60/AMI_Tx2.php';
        require_once $CLASSES_PATH . '60/AMI_Tx_Cmd_DB2.php';
        require_once $CLASSES_PATH . '60/AMI_Tx_Cmd_Storage2.php';
        require_once $CLASSES_PATH . '60/AMI_Package.php';
        require_once $CLASSES_PATH . '60/AMI_PackageManager2.php';
        */
        ### }

        $aVersion = AMI_Service::getVersion();
        $this->oRequest       = $oRequest;
        $this->oResponse      = $oResponse;
        $this->masterPassword = $CONNECT_OPTIONS['master_password'];
        $this->version        = $aVersion['cms']['code'];
        $this->setupTpl();
        if(!$this->checkAuth()){
            return;
        }

        // Authorized

        $path = AMI_Registry::get('path/root') . '_admin/_logs/tx.php';
        $canRollback = file_exists($path);

        if(!$canRollback){
            $this->parseTpl('no_rollback_data');

            return;
        }

        $aRollback = array_reverse(require_once($path));
        $txRollbackId = $oRequest->get('tx_rollback_id', FALSE, 'p');
        $action = $oRequest->get('do', FALSE, 'p');

        if($txRollbackId){
            switch($action){
                case 'rollback':
                    $this->rollback($aRollback);
                    // unlink($path);
                    break;
                /*
                case 'wipe_rollback_data':
                    $this->wipeRollbackData($aRollback);
                    break;
                */
                case 'wipe_all_backup_data':
                    $this->wipeAllBackupData();
                    break;
            }
        }else{
            $this->displayInfo($aRollback);
        }

        // d::w(d::getIncludedFilesHTML());###
    }

    /**
     * Setup template object.
     *
     * @return void
     */
    protected function setupTpl(){
        $locale = ADMIN_LOGIN_LANG != '' ? ADMIN_LOGIN_LANG : 'en';
        $locale = $this->oRequest->get('ami_locale', $locale);
        if(!in_array($locale, $this->aSupportedLocales)){
            $locale = 'en';
        }
        $this->oTpl = AMI::getResource('env/template_sys');
        $this->oTpl->setLocale($locale);
        $this->oTpl->addBlock('tx_rollback', '_shared/code/templates/modules/tx_rollback.tpl');
        $this->oTpl->setBlockLocale(
            'tx_rollback',
            $this->oTpl->parseLocale('_shared/code/templates/lang/tx_rollback.lng')
        );
    }

    /**
     * Check authentication.
     *
     * @return bool
     */
    protected function checkAuth(){
        $cookie = $this->oRequest->get($this->cookieName, FALSE, 'c');
        $password = $this->oRequest->get($this->cookieName, '', 'p');
        $authorized =
            ($cookie && $cookie == md5($this->masterPassword . $this->salt)) ||
            $password === $this->masterPassword;

        if($authorized){
            $this->oResponse->HTTP->setCookie(
                $this->cookieName,
                md5($this->masterPassword . $this->salt),
                time() + $this->cookieTTL
            );
        }else{
            if($password !== ''){
                $this->parseTpl('invalid_password');
            }
            $this->parseTpl('auth_form');
        }

        return $authorized;
    }

    /**
     * Display transaction info.
     *
     * @param  array $aRollback  Rollback data
     * @return void
     */
    protected function displayInfo(array $aRollback){
        $rows = '';
        $canRollback = TRUE;
        foreach($aRollback as $aRow){
            if($aRow['cmsVersion'] !== $this->version){
                $canRollback = FALSE;
                $aRow['cmsVersion'] =
                    $this->parseTpl(
                        'invalid_cms_version',
                        array(
                            'cmsVersion'        => $aRow['cmsVersion'],
                            'currentCMSVersion' => $this->version
                        ),
                        TRUE
                    );
            }
            $rows .= $this->parseTpl('row', $aRow, TRUE);
        }
        $this->parseTpl(
            'rollback',
            array(
                'rows'        => $rows,
                'canRollback' => $canRollback
            )
        );
    }

    /**
     * Offline rollback.
     *
     * @param  array $aRollback  Rollback data
     * @return void
     */
    protected function rollback(array $aRollback){
        AMI_Tx::log('Offline rollback is starting');
        foreach($aRollback as $aRow){
            if($aRow['cmsVersion'] !== $this->version){
                $this->parseTpl(
                    'invalid_cms_version',
                    array(
                        'cmsVersion'        => $aRow['cmsVersion'],
                        'currentCMSVersion' => $this->version
                    )
                );
                return;
            }

            $oTx = new $aRow['class'];
            $oTx->revert($aRow);
        }
        AMI_Tx::log('Offline rollback is finished');
        $this->parseTpl('rollback_success');
        $path = AMI_Registry::get('path/root') . '_admin/_logs/_tx.php';
        if(file_exists($path)){
            unlink($path);
        }
        rename(
            AMI_Registry::get('path/root') . '_admin/_logs/tx.php',
            $path
        );
    }

    /**
     * Wipes all backup data.
     *
     * @return void
     */
    protected function wipeAllBackupData(){
        $wipe = (bool)AMI::getSingleton('env/request')->get('wipe', FALSE, 'p');
        $aFS = array();
        $aTpl = array();
        $aTables = array();

        foreach(
            array(
                AMI_Registry::get('path/root') . '_local'                     => '_*.php',
                AMI_Registry::get('path/root') . '_local/eshop'               => '_*.php',
                AMI_Registry::get('path/hyper_local') . 'code'                => '_*.php',
                AMI_Registry::get('path/root') . '_local/_admin/images/icons' => '_*.gif'
            ) as $path => $fileMask
        ){
            $aFiles = AMI_Lib_FS::scan($path, $fileMask, '*', AMI_Lib_FS::SCAN_FILES, 1);
            if(sizeof($aFiles)){
                $aFS = array_merge($aFS, $aFiles);
            }
        }
        foreach(
            array(
                AMI_Registry::get('path/root') . '_local/_admin/_js'        => 'inst_*',
                AMI_Registry::get('path/root') . '_local/_admin/images'     => 'inst_*',
                AMI_Registry::get('path/root') . '_local/eshop/pay_drivers' => '*'
            ) as $path => $dirMask
        ){
            $aDirs = AMI_Lib_FS::scan($path, '*', $dirMask, AMI_Lib_FS::SCAN_DIRS, 1);
            foreach($aDirs as $dir){
                $aAllFiles = AMI_Lib_FS::scan($dir);
                $aBackupFiles = AMI_Lib_FS::scan($dir, '_*', '*', AMI_Lib_FS::SCAN_FILES);
                if(sizeof($aBackupFiles)){
                    $aFS = array_merge($aFS, $aBackupFiles);
                    if(sizeof($aBackupFiles) === sizeof($aAllFiles)){
                        $aFS[] = $dir;
                    }
                }elseif(!sizeof($aAllFiles)){
                    $aFS[] = $dir;
                }
            }
        }

        $oDB = AMI::getSingleton('db');

        foreach(array('', '_langs') as $tablePostfix){
            $oQuery = DB_Query::getSnippet(
                "SELECT `path`, `name` " .
                "FROM `cms_modules_templates" . $tablePostfix . "` " .
                "WHERE `name` LIKE %s " .
                "ORDER BY `name` ASC, `path` ASC"
            )->q('\_inst_%');
            $oRecords = $oDB->select($oQuery);
            foreach($oRecords as $aRow){
                $aTpl[] = $aRow['path'] . $aRow['name'];
            }
        }

        $oQuery = DB_Query::getSnippet("SHOW TABLES LIKE %s")->q('cms\_inst\_%\_');
        $oRecords = $oDB->select($oQuery, MYSQL_NUM);
        foreach($oRecords as $aRow){
            $aTables[] = $aRow[0];
        }

        if($wipe){
            foreach($aFS as $path){
                if(!is_dir($path)){
                    unlink($path);
                }else{
                    rmdir($path);
                }
            }
            foreach($aTpl as $path){
                $name = basename($path);
                $path = dirname($path) . '/';
                $table = 'cms_modules_templates' . (preg_match('/\.lng$/', $name) ? '_langs' : '');
                $oQuery = DB_Query::getSnippet(
                    "DELETE FROM `%s` " .
                    "WHERE `path` = %s AND `name` = %s "
                )
                ->plain($table)
                ->q($path)
                ->q($name);
                $oDB->query($oQuery);
            }
            foreach($aTables as $table){
                $oQuery =
                    DB_Query::getSnippet("DROP TABLE IF EXISTS `%s`")
                    ->plain($table);
                $oDB->query($oQuery, AMI_DB::QUERY_TRUSTED);
            }
            $this->parseTpl('wipe_all_backup_data_result', array(
                'tables_deleted' => sizeof($aTables),
                'files_deleted'  => sizeof($aFS),
                'tpls_deleted'   => sizeof($aTpl),
            ));
        }else{
            $aScope = array('db_rows' => '', 'fs_rows' => '', 'tpl_rows' => '');
            foreach($aTables as $path){
                $aScope['db_rows'] .= $this->parseTpl('data_row', array('path' => $path), TRUE);
            }
            foreach($aFS as $path){
                $aScope['fs_rows'] .= $this->parseTpl('data_row', array('path' => $path), TRUE);
            }
            foreach($aTpl as $path){
                $aScope['tpl_rows'] .= $this->parseTpl('data_row', array('path' => $path), TRUE);
            }
            $this->parseTpl('wipe_all_backup_data', $aScope);
        }
    }

    /**
     * Parses and displays or returns template set data.
     *
     * @param  string $set     Set name
     * @param  array  $aScope  Scope
     * @param  bool   $return  Flag specifying to return data
     * @return void|string
     */
    protected function parseTpl($set, array $aScope = array(), $return = FALSE){
        $content = $this->oTpl->parse('tx_rollback:' . $set, $aScope);
        if($return){
            return $content;
        }else{
            $this->oResponse->write($content);
        }
    }
}
