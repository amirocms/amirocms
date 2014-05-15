<?php
/**
 * AmiExt/Tags extension configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiExt_Tags
 * @version   $Id: AmiExt_Tags_Adm.php 42166 2013-10-10 12:29:32Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiExt/Tags extension configuration admin controller.
 *
 * @package    Config_AmiExt_Tags
 * @subpackage Controller
 * @resource   ext_tags/module/controller/adm <code>AMI::getResource('ext_tags/module/controller/adm')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_Tags_Adm extends Hyper_AmiExt{
    /**
     * Variable to save tags info
     *
     * @var string
     */
    private $aTags = array();

    /**
     * Callback called after module is installed.
     *
     * Alers module table to add extension fields.
     *
     * @param  string         $modId  Installed module id
     * @param  AMI_Tx_Cmd_Args $oArgs  Transaction command arguments
     * @return void
     */
    public function onModPostInstall($modId, AMI_Tx_Cmd_Args $oArgs){
        global $db;

        $oTable = AMI::getResourceModel($modId . '/table');
        $table = $oTable->getTableName();
        $db->setSafeSQLOptions('alter');
        $sql =
            "ALTER TABLE " . $table . " " .
            "ADD `tags` varchar(255) NOT NULL DEFAULT ''";
        $db->query($sql);
    }

    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */

    /**
     * Extension initialization.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Ext::__construct()
     * @see    AMI_Mod::init()
     */
    public function handlePreInit($name, array $aEvent, $handlerModId, $srcModId){
        $oView = $this->getView(AMI_Registry::get('side'));
        $oView->setExt($this);
        AMI_Event::addHandler('on_form_fields_form', array($oView, 'handleForm'), $aEvent['modId']);
        AMI_Event::addHandler('on_before_save_model_item', array($this, 'handleBeforeSaveModelItem'), $aEvent['modId']);
        AMI_Event::addHandler('on_after_save_model_item', array($this, 'handleAfterSaveModelItem'), $aEvent['modId']);
        AMI_Event::addHandler('on_after_delete_model_item', array($this, 'handleAfterDeleteModelItem'), $aEvent['modId']);

        return $aEvent;
    }

     /**
      * Handle before save model.
      *
      * @param  string $name          Event name
      * @param  array  $aEvent        Event data
      * @param  string $handlerModId  Handler module id
      * @param  string $srcModId      Source module id
      * @return array
      */
    public function handleBeforeSaveModelItem($name, array $aEvent, $handlerModId, $srcModId){

        if($aEvent['onCreate']){
            // New element
            if(trim($aEvent['aData']['tags'])){

                $aEvent['aData']['tags'] = AMI_Lib_String::htmlChars($aEvent['aData']['tags']);

                $cTags = str_replace(";", ",", $aEvent['aData']['tags']);
                $aTags = explode(",", $cTags);
                $aTags = array_unique($aTags);
                if(count($aTags) > 0){
                    $modName = "srv_tags";
                    $oModule = $GLOBALS['cms']->Core->GetModule($modName);
                    if(is_object($oModule) && $oModule->IsInstalled()){
                        $oDB = new DB_si;
                        $oModule->InitEngine($GLOBALS['cms'], $oDB);
                        $oModule->Engine->Init(array(), 'templates/lang/_' . $modName . '_msgs.lng', 'templates/lang/' . $modName . '.lng');
                    }

                    foreach($aTags as $tag){
                        $tag = trim($tag);
                        $oQuery = new DB_Query('cms_tags');
                        $oQuery->addFields(array('id'));
                        $oQuery->addWhereDef(DB_Query::getSnippet('AND lower(tag) = %s')->q(mb_strtolower($tag)));
                        $oRS = AMI::getSingleton('db')->select($oQuery);
                        if($oRS->count()){
                            $aRecord = $oRS->current();
                            // tag already exists in the table
                            $tags[] = $aRecord['id'];
                            // increase counter of this tag
                        }else{
                            // add tag into table and get its id
                            // store all arrays: Vars, VarsPost, VarsGet
                            $aVarsPost = $GLOBALS['cms']->VarsPost;
                            $aVarsGet = $GLOBALS['cms']->VarsGet;
                            $aVars = $GLOBALS['cms']->Vars;
                            // prepare new array, which are used in module
                            $GLOBALS['cms']->VarsPost = array("tag" => $tag, "sublink" => "");
                            $GLOBALS['cms']->VarsGet = array();
                            $GLOBALS['cms']->Vars = array();
                            if(is_object($oModule) && $oModule->IsInstalled()){
                                $oModule->Engine->ProcessAction("add", $cId);
                            }
                            // restore arrays
                            $GLOBALS['cms']->VarsPost = $aVarsPost;
                            $GLOBALS['cms']->VarsGet = $aVarsGet;
                            $GLOBALS['cms']->Vars = $aVars;

                            // get id of inserted tag
                            $oQuery = new DB_Query('cms_tags');
                            $oQuery->addFields(array('id'));
                            $oQuery->addWhereDef(DB_Query::getSnippet('AND tag = %s')->q($tag));
                            $oRS = AMI::getSingleton('db')->select($oQuery);
                            if($oRS->count()){
                                $aRecord = $oRS->current();
                                // tag already exists in the table
                                $tags[] = $aRecord['id'];
                                // increase counter of this tag
                            }
                        }
                    }
                    unset($oModule);
                    $tags = array_unique($tags);    // remove duplicated tags if they are present present

                    $tagsSql = implode(",", $tags);

                    // update tags counters
                    if(count($tags) > 0){
                        $sql = "UPDATE `cms_tags` SET count = count + 1, tmp_count = tmp_count + 1 WHERE id IN (".$tagsSql.")";
                        AMI::getSingleton('db')->query(
                            DB_Query::getUpdateQuery(
                                'cms_tags',
                                array(
                                    'count'  => DB_Query::getSnippet('count + 1'),
                                    'tmp_count' => DB_Query::getSnippet('tmp_count + 1'),
                                ),
                                DB_Query::getSnippet('WHERE id IN (%s)')->plain($tagsSql)
                            )
                        );

                        $this->aTags = $aEvent['aData']['tags'] = $aEvent['oItem']->tags = $tagsSql;
                        $this->updateTagsData($aEvent['oItem']->id);
                    }
                }
                $this->updateTagsData($aEvent['oItem']->id);
            }
        }else{
            // Update existing element
            $aTagsOld = array();  // Previous tags ids.
            $aTagsDel = array();  // Deleted tags ids. Counters should be decreased.

            $oQuery = new DB_Query(AMI::getResourceModel($srcModId.'/table')->getTableName());
            $oQuery->addFields(array('tags'));
            $oQuery->addWhereDef(DB_Query::getSnippet('AND `id` = %s')->q($aEvent['oItem']->id));

            $oRS = AMI::getSingleton('db')->select($oQuery);

            if($oRS->count()){
                foreach($oRS as $aRow){
                    $aTagsOld += explode(",", $aRow['tags']);
                }
            }

            $aEvent['aData']['ext_tags'] = $aEvent['oItem']->ext_tags = $aEvent['aData']['tags'];
            $aEvent['aData']['tags'] = AMI_Lib_String::htmlChars($aEvent['aData']['tags']);

            $cTags = str_replace(";", ",", $aEvent['aData']['tags']);
            $aTags = explode(",", $cTags);
            $aTags = array_unique($aTags);
            $tags = array();

            // Call old module
            $modName = "srv_tags";
            $oModule = $GLOBALS['cms']->Core->GetModule($modName);

            if(is_object($oModule) && $oModule->IsInstalled()){
                $oDB = new DB_si;
                $oModule->InitEngine($GLOBALS['cms'], $oDB);
                $oModule->Engine->Init(array(), 'templates/lang/_' . $modName . '_msgs.lng', 'templates/lang/' . $modName . '.lng');
            }

            foreach($aTags as $tag){
                $tag = trim($tag);

                $oQuery = new DB_Query('cms_tags');
                $oQuery->addFields(array('id'));
                $oQuery->addWhereDef(DB_Query::getSnippet('AND lower(tag) = %s')->q(mb_strtolower($tag)));
                $oRS = AMI::getSingleton('db')->select($oQuery);

                if($oRS->count()){
                    // tag exists
                    $aRecord = $oRS->current();
                    $tags[] = $aRecord['id'];
                    // if it has not been deleted
                    if(!in_array($aRecord['id'], $aTagsOld)){
                    // new tag, increase counter
                        AMI::getSingleton('db')->query(
                            DB_Query::getUpdateQuery(
                                'cms_tags',
                                array(
                                    'count'  => DB_Query::getSnippet('count + 1'),
                                    'tmp_count' => DB_Query::getSnippet('tmp_count + 1'),
                                ),
                                DB_Query::getSnippet('WHERE id = %s')->q($aRecord['id'])
                            )
                        );
                    }else{
                        // delete tag from $aTagsOld
                        $index = array_search($aRecord['id'], $aTagsOld);
                        unset($aTagsOld[$index]);
                    }
                }else{
                    // add it if not in table
                    if(!empty($tag)){
                        // store all arrays: Vars, VarsPost, VarsGet
                        $aVarsPost = $GLOBALS['cms']->VarsPost;
                        $aVarsGet = $GLOBALS['cms']->VarsGet;
                        $aVars = $GLOBALS['cms']->Vars;
                        // prepare new array, which are used in module
                        $GLOBALS['cms']->VarsPost = array("tag" => $tag, "sublink" => "");
                        $GLOBALS['cms']->VarsGet = array();
                        $GLOBALS['cms']->Vars = array();

                        if(is_object($oModule) && $oModule->IsInstalled()){
                            $oModule->Engine->ProcessAction("add", $cId);
                        }

                        // restore arrays
                        $GLOBALS['cms']->VarsPost = $aVarsPost;
                        $GLOBALS['cms']->VarsGet = $aVarsGet;
                        $GLOBALS['cms']->Vars = $aVars;

                        $oQuery = new DB_Query('cms_tags');
                        $oQuery->addFields(array('id'));
                        $oQuery->addWhereDef(DB_Query::getSnippet('AND lower(tag) = %s')->q(mb_strtolower($tag)));
                        $oRS = AMI::getSingleton('db')->select($oQuery);
                        if($oRS->count()){
                            $aRecord = $oRS->current();
                            $tags[] = $aRecord['id'];
                            AMI::getSingleton('db')->query(
                                DB_Query::getUpdateQuery(
                                    'cms_tags',
                                    array(
                                        'count'  => DB_Query::getSnippet('count + 1'),
                                        'tmp_count' => DB_Query::getSnippet('tmp_count + 1'),
                                    ),
                                    DB_Query::getSnippet('WHERE id = %s')->q($aRecord['id'])
                                )
                            );
                        }
                    }
                }
            }

            // Update counters
            foreach($aTagsOld as $value){
                AMI::getSingleton('db')->query(
                    DB_Query::getUpdateQuery(
                        'cms_tags',
                        array(
                            'count'  => DB_Query::getSnippet('count - 1'),
                            'tmp_count' => DB_Query::getSnippet('tmp_count - 1'),
                        ),
                        DB_Query::getSnippet('WHERE id = %s')->q($value)
                    )
                );
            }

            $this->aTags = implode(",", $tags);
            $aEvent['aData']['tags'] = $this->aTags;
          	$aEvent['oItem']->tags = $this->aTags;

            $this->updateTagsData($aEvent['oItem']->id);
        }

        return $aEvent;
    }

    /**
     * Handle after save model.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleAfterSaveModelItem($name, array $aEvent, $handlerModId, $srcModId){

        if($aEvent['onCreate']){
            $this->updateTagsData($aEvent['oItem']->id);
        }else{
            $tags = "";
            $aTags = array();

            if($aEvent['oItem']->tags !== ""){
                if($this->aTags === ""){
                    $this->aTags = $aEvent['oItem']->tags;
                }
                if($this->aTags != ''){
                    $this->aTags = trim($this->aTags, ',');
                    $oQuery = new DB_Query('cms_tags');
                    $oQuery->addFields(array('tag'));
                    $oQuery->addWhereDef(DB_Query::getSnippet('AND `id` IN (%s)')->plain($this->aTags));
                    $oRS = AMI::getSingleton('db')->select($oQuery);
                    if($oRS->count()){
                        foreach($oRS as $aRow){
                            $aTags[] = AMI_Lib_String::unhtmlEntities($aRow['tag']);
                        }
                    }
                    $aEvent['oItem']->tags = implode(",", $aTags);
                }
            }
        }
        return $aEvent;
    }

     /**
      * Handle after delete model.
      *
      * @param  string $name          Event name
      * @param  array  $aEvent        Event data
      * @param  string $handlerModId  Handler module id
      * @param  string $srcModId      Source module id
      * @return array
      */
    public function handleAfterDeleteModelItem($name, array $aEvent, $handlerModId, $srcModId){
        // correct counters of tags
        $oQuery = new DB_Query(AMI::getResourceModel($srcModId.'/table')->getTableName());
        $oQuery->addFields(array('tags'));
        $oQuery->addWhereDef(DB_Query::getSnippet('AND `id` = %s')->q($aEvent['oItem']->id));
        $oRS = AMI::getSingleton('db')->select($oQuery);
        if($oRS->count()){
            $aRecord = $oRS->current();
            $tags = $aRecord['tags'];
            if($tags != ""){
                $tags = trim($tags, ",");
                AMI::getSingleton('db')->query(
                    DB_Query::getUpdateQuery(
                        'cms_tags',
                        array(
                            'count'  => DB_Query::getSnippet('count - 1'),
                            'tmp_count' => DB_Query::getSnippet('tmp_count - 1'),
                        ),
                        DB_Query::getSnippet('WHERE id IN (%s)')->plain($tags)
                    )
                );
                $this->updateTagsData($aEvent['oItem']->id);
            }
        }
        return $aEvent;
    }

    /**#@-*/

    /**
     * Update tags data for specified id.
     *
     * @param int $cId  Id.
     * @return void
     */
    function updateTagsData($cId){
        if($this->checkModOption("extensions")){
            $optExtensions = $this->getModOption("extensions");
            if(is_array($optExtensions) && !in_array("ext_reindex", $optExtensions)){
                // create module reindex functions
                // require_once $GLOBALS['CLASSES_PATH'] . 'CMS_Functions.php';
                // require_once $GLOBALS['CLASSES_PATH'] . 'ModuleReindexFunctions.php';
                // AMI::getSingleton('Core')->GetModule;
                $oModule = $GLOBALS['cms']->Core->GetModule($this->modId);
                $oDb = new DB_si;
                $modReindex = new ModuleReindexFunctions($GLOBALS['cms'], $oDb, $oModule, $oModule);
                if(is_object($modReindex)){
                    $modReindex->reindexById($cId, $oModule, $oDb, AMI_Registry::get('lang_data', 'en'));
                }
                AMI::getSingleton('db')->query(
                    DB_Query::getUpdateQuery(
                        'cms_site_search',
                        array(
                            'tags'  => DB_Query::getSnippet('%s')->q(is_array($this->aTags)?implode(",", $this->aTags):$this->aTags),
                        ),
                        DB_Query::getSnippet('WHERE id_item = %s AND module_name = %s')->q($cId)->q($this->modId)
                    )
                );
            }
        }

        $oQuery = new DB_Query('cms_tags');
        $oQuery->addFields(array('MAX(count) AS counter'));
        $oRS = AMI::getSingleton('db')->select($oQuery);
        if($oRS->count()){
            $aRecord = $oRS->current();
            $counter = $aRecord['counter'];
            $GLOBALS['cms']->Core->WriteOption("srv_tags", "tags_elements_count", $counter);
        }
    }

    /**
     * Returns comma-separated tags values by it comma-separated IDs.
     *
     * @param  string $idTags  Tags id's
     * @return string
     */
    public function getTagsByIds($idTags = ''){
        $tags = '';

        if($idTags){
            $aTags = array();
            $oQuery = new DB_Query('cms_tags');
            $oQuery->addFields(array('tag'));
            $oQuery->addWhereDef(DB_Query::getSnippet('AND `id` IN (%s)')->plain(trim($idTags, ',')));

            $oRS = AMI::getSingleton('db')->select($oQuery);
            if($oRS->count()){
                foreach($oRS as $aRow){
                    $aTags[] = AMI_Lib_String::unhtmlEntities($aRow['tag']);
                }
            }
            if(sizeof($aTags)){
                $tags = implode(',', $aTags);
            }
        }

        return $tags;
    }
}

/**
 * AmiExt/Tags extension configuration admin view.
 *
 * @package    Config_AmiExt_Tags
 * @subpackage View
 * @resource   ext_tags/view/adm <code>AMI::getResource('ext_tags/view/adm')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_Tags_ViewAdm extends AMI_ExtView{
    /**
     * Template block name
     *
     * @var string
     */
    protected $tplBlockName = 'tags_ext';

    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */

    /**
     * Adds tags field onto admin form.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleForm($name, array $aEvent, $handlerModId, $srcModId){
        if(is_object($aEvent['oFormView'])){
            $oFormView = $aEvent['oFormView'];

            if($oFormView instanceof AMI_iModFormView){
                $oTpl = $this->getTemplate();
                $oTpl->setBlockLocale($this->tplBlockName, $this->aLocale);
                $aScope = $aEvent['aScope'];
                $oFormView->addTemplate($this->tplFileName, 'ext_tags', $this->aLocale);
                if(!isset($aEvent['oItem']->ext_tags) || !isset($aEvent['oItem']->tags)){
                	$aScope['ext_tags'] = $this->oExt->getTagsByIds($aEvent['oItem']->tags);
                }else{
                	$aScope['ext_tags'] = $aEvent['oItem']->tags;
                }
                $oFormView->addField(array('name' => 'ext_tags', 'position' => 'ext_tags.end', 'html' => $oTpl->parse($this->tplBlockName . ':ext_tags_field', $aScope)));
            }
        }
        return $aEvent;
    }

    /**#@-*/
}
