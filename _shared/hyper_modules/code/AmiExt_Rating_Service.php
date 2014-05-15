<?php
/**
 * AmiExt/Rating extension configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiExt_Rating
 * @version   $Id: AmiExt_Rating_Service.php 44499 2013-11-27 07:54:24Z Kolesnikov Artem $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiExt/Rating extension configuration service class.
 *
 * @package    Config_AmiExt_Rating
 * @subpackage Controller
 * @resource   ext_rating/service <code>AMI::getResource('ext_rating/service')</code>
 * @amidev     Temporary
 */
class AmiExt_Rating_Service{
    /**
     * Instance
     *
     * @var ExtRating_Service
     */
    private static $oInstance;

    /**
     * Bitwise options names: "allow_ratings" etc.
     *
     * @var array
     */
    private static $aRateOptions = array("allow_ratings", "display_ratings", "sort_by_ratings", "display_votes");

    /**
     * Extension settings
     *
     * @var array
     */
    private static $aSettings = array();

    /**
     * Returns ExtRating_Service instance.
     *
     * @return ExtRating_Service
     */
    public static function getInstance(){
        if(self::$oInstance == null){
            self::$oInstance = new self;
        }
        return self::$oInstance;
    }

    /**
     * Destroys instance.
     *
     * @return void
     * @todo   Use when all business logic processed or detect usage necessity
     */
    public static function destroyInstance(){
        self::$oInstance = null;
    }

    /**
     * Dispatches raw service action.
     *
     * @return void
     * @amidev
     */
    public static function dispatchAction(){
        $oRequest = AMI::getSingleton('env/request');
        switch($oRequest->get('action')){
            case 'rate':

                $modId = "ext_rating";
                $oCms = $GLOBALS['frn'];

                // #CMS-11482 {

                $formTemplate =
                    AMI::issetOption($oCms->VarsPost['id_module'], 'form_template')
                        ? AMI::getOption($oCms->VarsPost['id_module'], 'form_template')
                        : AMI::getOption($modId, 'form_template');
                AMI_Registry::get('oGUI')->addBlock('ext_rating', 'templates/modules/ext_' . $formTemplate);

                // } #CMS-11482

                self::$aSettings['numRatingPics'] = 5;
                self::$aSettings["modId"] = $oCms->VarsPost['id_module'];
                self::$aSettings["defaultRating"] = floatval(AMI::getOption($modId, "default_rating"));
                self::$aSettings["decimalPlaces"] = intval(AMI::getOption($modId, 'rating_decimal_places'));
                self::$aSettings["minVotesToDisplay"] = AMI::getOption($modId, "minimum_votes_to_display");
                self::$aSettings["gradeSize"] = intval(AMI::getOption($modId, "grade_size"));
                self::$aSettings["formType"] = AMI::getOption($modId, "form_type");
                self::$aSettings["userLoggedIn"] = is_object($oCms->Member) && $oCms->Member->isLoggedIn();
                self::$aSettings["catModuleName"] = AMI::isCategoryModule(self::$aSettings["modId"]) ? self::$aSettings["modId"] : self::$aSettings["modId"] . '_cat';
                self::$aSettings["catRatingsEnabled"] = false;
                if(AMI_ModDeclarator::getInstance()->isRegistered(self::$aSettings["catModuleName"])){
                    self::$aSettings["catRatingsEnabled"] = AMI::issetOption(self::$aSettings["catModuleName"], 'extensions') && in_array($modId, AMI::getOption(self::$aSettings["catModuleName"], "extensions"));
                }

                self::$aSettings["userId"] = 0;
                if(self::$aSettings["userLoggedIn"]){
                    self::$aSettings["userId"] = $oCms->Member->getUserInfo("id");
                }
                self::$aSettings["ip"] = ip2long(getenv('REMOTE_ADDR'));
                if(isset($oCms->VarsCookie['vid'])){
                    self::$aSettings["vid"] = $oCms->VarsCookie['vid'];
                }else{
                    self::$aSettings["vid"] = md5(getenv('REMOTE_ADDR') . ':' . rand(0, 1000000) . ':' . microtime());
                    SetLocalCookie('vid', self::$aSettings["vid"], time() + 315360000);
                }

                AMI_Service::hideDebug();
                self::processRequest();
                self::processAjaxRequest();
                break;
            default:
                break;
        }
    }


    /**
     * Processes request and calculates new rating data.
     *
     * @return void
     */
    private static function processRequest(){
        $oCms = $GLOBALS['frn'];
        $langFile = AMI_Registry::get('oGUI')->ParseLangFile("templates/lang/_rating_msgs.lng");
        $oCms->InitMessages($langFile);

        // if we have "rating_by_registered_only" set to "false"
        // or a user is logged in - proceed.
        $userLoggedIn = self::$aSettings["userLoggedIn"];
        $ratingOK = !AMI::getOption("ext_rating", "rating_by_registered_only") || $userLoggedIn;
        if($ratingOK){
            self::clearRatingHistory();
            // fetch the id (or catID) of rated item (or Cat), table and module names
            if(isset($oCms->VarsPost["id_item"]) && $oCms->VarsPost["id_item"] != ""){
                $ratedID = intval($oCms->VarsPost["id_item"]);
                $modId = $oCms->VarsPost["id_module"];
                $oTable = AMI::getResourceModel($modId . '/table');
                $tableName = $oTable->getTableName();
                if($modId == 'eshop_item' && AMI::issetAndTrueOption($modId, "item_links_allowed")){
                    // check this is link
                    $sql = "SELECT `id_source` FROM %s WHERE `id` = %s";
                    $aRecord = AMI::getSingleton('db')->fetchRow(DB_Query::getSnippet($sql)->plain($tableName)->plain(intval($ratedID)));
                    if(isset($aRecord['id_source']) && $aRecord['id_source']){
                        $linkRatedID = $ratedID;
                        $ratedID = $aRecord['id_source'];
                    }
                    // check it has links
                    $sql = "SELECT `id`,`id_category` FROM %s WHERE `id_source` = %s";
                    $oRS = AMI::getSingleton('db')->select(DB_Query::getSnippet($sql)->plain($tableName)->plain(intval($ratedID)));
                    $aLinksRatedID = array();
                    foreach($oRS as $aRecord){
                        $aLinksRatedID[] = $aRecord;
                    }
                }
            }elseif(isset($oCms->VarsPost["cat_id"]) && $oCms->VarsPost["cat_id"] != ""){
                $ratedID = intval($oCms->VarsPost["cat_id"]);
                $modId = $oCms->VarsPost["id_module"] . "_cat";
                $tableName = AMI::getResourceModel($modId . '/table')->getTableName();
            }elseif(isset($oCms->VarsPost["catid"]) && $oCms->VarsPost["catid"] != ""){
                $ratedID = intval($oCms->VarsPost["catid"]);
                $modId = $oCms->VarsPost["id_module"] . "_cat";
                $tableName = AMI::getResourceModel($modId . '/table')->getTableName();
            }
            $rated = true;
            if(isset($ratedID)){
                $rated = self::isAlreadyRated($ratedID);
            }
            $frontModuleName = $modId;
            self::$aSettings["modId"] = $frontModuleName;

            // user already rated this item
            if($rated == true){
                if(isset($langFile["status_error_" . $frontModuleName])){
                    $oCms->AddStatusMsg("status_error_" . $frontModuleName, "red");
                }else{
                    $oCms->AddStatusMsg("status_error_news", "red");
                }
                unset($oCms->VarsGet['status_msg'], $oCms->VarsCookie['status_msg']);
            }else{
                // insert into rating history
                $aIns = Array(
                    'id'        => "LAST_INSERT_ID()",
                    'id_module' => $frontModuleName,
                    'id_item'   => $ratedID,
                    'ip'        => self::$aSettings['ip'],
                    'vid'       => self::$aSettings['vid'],
                    'id_user'   => 0
                );

                if($userLoggedIn){
                    $aIns["id_user"] = self::$aSettings["userId"];
                    $expireTime = strtotime(AMI::getOption("ext_rating", "history_allow_same_user"));
                    $aIns["date_expire_user"] = date('Y-m-d H-i-s', $expireTime);
                }else{
                    $expireTime = strtotime(AMI::getOption("ext_rating", "history_allow_same_ip"));
                    $aIns["date_expire_ip"] = date('Y-m-d H-i-s', $expireTime);
                }

                // #CMS-11175 {
                /**
                 * Allows to collect information about ratings in Raeting extension.
                 *
                 * @event      on_rate ext_rating
                 * @eventparam string modId   Module id
                 * @eventparam int    itemId  Module item id
                 * @eventparam string rating  Passed rating
                 * @eventparam int    userId  Authorized user id or 0 if not authorized
                 * @eventparam string vid     Site visitor id
                 */
                $aEvent = array(
                    'modId'  => $frontModuleName,
                    'itemId' => $ratedID,
                    'rating' => $oCms->VarsPost['rating'],
                    'userId' => $userLoggedIn ? self::$aSettings['userId'] : 0,
                    'vid'    => self::$aSettings['vid']
                );
                AMI_Event::fire('on_rate', $aEvent, 'ext_rating');
                // } #CMS-11175

                $expireTime = strtotime(AMI::getOption("ext_rating", "history_allow_same_vid"));
                $aIns["date_expire_vid"] = date('Y-m-d H-i-s', $expireTime);

                AMI::getSingleton('db')->query(DB_Query::getInsertQuery("cms_rate_history", $aIns));

                $doMapping = $oTable->hasField('votes_count');
                $aFields =
                    $doMapping
                        ? array(
                            'count'  => 'votes_count',
                            'weight' => 'votes_weight',
                            'rating' => 'votes_rate'
                        )
                        : array(
                            'count'  => 'ext_rate_count',
                            'weight' => 'ext_rate_weight',
                            'rating' => 'ext_rate_rate'
                        );
                // fetch previous ratings
                $sql =
                    "SELECT " . $aFields['count'] . ", " . $aFields['weight'] . ", " . $aFields['rating'] . " " .
                    "FROM " . $tableName . " " .
                    "WHERE `id` = " . $ratedID;
                $aRecord = AMI::getSingleton('db')->fetchRow(DB_Query::getSnippet($sql));
                if($aRecord && count($aRecord)){
                    $count = intval($aRecord[$aFields['count']]);
                    $weight = intval($aRecord[$aFields['weight']]);
                    $rating = floatval($aRecord[$aFields['rating']]);
                }

                // recalculate counters, weights & ratings
                $rval = 1 +  (intval($oCms->VarsPost["rating"])*(AMI::getOption("ext_rating", "default_rating") * 2 - 2) / (AMI::getOption("ext_rating", "grade_size") - 1));
                $userWeight = 1; // default weight (for unregistered users and unweighted ratings)

                if(AMI::getOption("ext_rating", "weighted_rating") && $userLoggedIn ){
                    // evaluate the user's weight using the formula from options
                    $regdays = intval((time() - $oCms->Member->getUserInfo("date_timestamp")) / (24 * 60 * 60));
                    $formula = str_replace(" ", "", AMI::getOption("ext_rating", "weight_formula"));
                    $varR = "((\$regdays+";
                    $formula = str_replace($varR, "", $formula);
                    $aFormula = mb_split(")", $formula);

                    if((count($aFormula) > 2) && is_numeric($aFormula[0]) && is_numeric(str_replace("/", "", $aFormula[1])) && is_numeric(str_replace("+", "", $aFormula[2]))){
                        eval("\$userWeight = floatval(" . AMI::getOption("weight_formula") . ");");
                    }
                }

                if(AMI::getOption("ext_rating", "weighted_rating") == "true"){
                    // recalculate weighted
                    $rating = (($rval * $userWeight) + ($rating * $weight)) / ($weight + $userWeight);
                }else{
                    // recalculate unweighted
                    $rating = (($rval) + ($rating * $count)) / ($count + 1);
                }

                // update the table with the new values
                $aUpd = array(
                    $aFields['count']  => $count + 1,
                    $aFields['weight'] => $weight + $userWeight,
                    $aFields['rating'] => $rating
                );

                if(!empty($aLinksRatedID)){
                    $linksIDs = '';
                    foreach($aLinksRatedID as $aRecord){
                        $linksIDs .= $aRecord['id'] . ',';
                    }
                    $linksIDs = trim($linksIDs, ',');
                    $condition = "WHERE id IN (" . $ratedID . "," . $linksIDs . ")";
                }else{
                    $condition = "WHERE id=" . $ratedID;
                }
                AMI::getSingleton('db')->query(DB_Query::getUpdateQuery($tableName, $aUpd, $condition));

                if(self::$aSettings["catRatingsEnabled"] && AMI::getOption(self::$aSettings["catModuleName"], "average_cat_rating")){
                    self::setAverageRatings();
                    if(!empty($aLinksRatedID)){
                        foreach($aLinksRatedID as $aRecord){
                            self::setAverageRatings($aRecord['id_category']);
                        }
                    }
                }
                $oCms->AddStatusMsg("status_add");

                // disable possible discussion message
                unset($oCms->VarsGet['status_msg'], $oCms->VarsCookie['status_msg']);
            }
        }else{
            // warn of failure and invite to register
            $registerLink = "";
            if($oCms->Core->IsInstalled("members")){
                $mMembers = $oCms->Core->GetModule("members");
                $registerLink = $mMembers->GetFrontLink();
            }
            $oCms->AddStatusMsg("status_please_register", "red", "", "", array('registerlink' => $registerLink));
        }
    }

    /**
     * Processes item data and returns AJAX response.
     *
     * @return void
     */
    private static function processAjaxRequest(){
        $cookieString = '';

        $oCms = $GLOBALS['frn'];
        $vid = self::$aSettings['vid'];

        $oTable = AMI::getResourceModel(self::$aSettings['modId'] . '/table');
        $oItem = $oTable->find($oCms->VarsPost['id_item'], array("*"));
        $aItem = $oItem->getData();

        $doMapping = $oTable->hasField('votes_count');
        $aFields =
            $doMapping
                ? array(
                    'count'  => 'votes_count',
                    'weight' => 'votes_weight',
                    'rating' => 'votes_rate'
                )
                : array(
                    'count'  => 'ext_rate_count',
                    'weight' => 'ext_rate_weight',
                    'rating' => 'ext_rate_rate'
                );

        $itemOptions = "0000000" . decbin($aItem["ext_rate_opt"]);

        $aItem["rating_img"] = round(((self::$aSettings['numRatingPics']) * ($aItem[$aFields['rating']])) / ((self::$aSettings["defaultRating"]) * 2 - 1), 0);
        $aItem["votes_rate"] = round($aItem[$aFields['rating']], self::$aSettings["decimalPlaces"]);
        $aItem["votes_count"] = $aItem[$aFields['count']];

        $aItem['rating_block'] = $aItem['votes_block'] = '';

        if(AMI::getOption("ext_rating", "minimum_votes_to_display") <= $aItem[$aFields['count']]){
            if(AmiExt_Rating_Frn::getOptionBit($itemOptions, array_search("display_ratings", self::$aRateOptions))){
                $aItem["rating_block"] = AMI_Registry::get('oGUI')->get("ext_rating:rating_block", $aItem);
            }
            if(AmiExt_Rating_Frn::getOptionBit($itemOptions, array_search("display_votes", self::$aRateOptions))){
                $aItem["votes_block"] = AMI_Registry::get('oGUI')->get("ext_rating:votes_block", $aItem);
            }
        }

        $ratingBlock = $aItem["rating_block"];
        $votesBlock = $aItem["votes_block"];

        // Calc cookie expiration time
        $expTimeUser = strtotime(AMI::getOption("ext_rating", "history_allow_same_user"));
        $expTimeIP = strtotime(AMI::getOption("ext_rating", "history_allow_same_ip"));
        $expTimeVid = strtotime(AMI::getOption("ext_rating", "history_allow_same_vid"));
        $cookieExpTime = min(array($expTimeUser, $expTimeIP, $expTimeVid));

        // Get history data and prepare the cookie string
        $aModuleRatings = array();
        $sql = "SELECT id_module, id_item FROM cms_rate_history WHERE vid=%s ORDER BY id DESC LIMIT 150";
        $oRecordset = AMI::getSingleton('db')->select(DB_Query::getSnippet($sql)->q($vid));
        foreach($oRecordset as $aRecord){
            $module = $aRecord["id_module"];
            $item = $aRecord["id_item"];
            if(!isset($aModuleRatings[$module])){
                $aModuleRatings[$module] = array();
            }
            if(array_search($item, $aModuleRatings[$module]) === false){
                $aModuleRatings[$module][] = $item;
            }
        }
        if(count($aModuleRatings)){
            $moduleRatings = '';
            foreach($aModuleRatings as $module => $aRatings){
                $moduleRatings .= $module . ':' . implode(':', $aRatings). ';';
            }
            // Calculate urlencoded string size in bytes and cut everything over 1000 bytes
            $checkStr = str_replace(':', '%3A', $moduleRatings);
            $checkStr = str_replace(';', '%3B', $checkStr);
            if(strlen($checkStr) > 1000 ){
                $checkStr = substr($checkStr, 0, 1000);
                $checkStr = substr($checkStr, 0, strrpos($checkStr, '%3A'));
                $checkStr = str_replace('%3A', ':', $checkStr);
                $moduleRatings = str_replace('%3B', ';', $checkStr);
                $moduleRatings .= ';';
            }
            $cookieString = $moduleRatings;
        }

        $aSysMsgs = array();
        $aPlainMsgs = array();
        $statusMessages = $oCms->getStatusMsgArrays($aSysMsgs, $aPlainMsgs);
        $aStatusMessages = array_merge($aSysMsgs, $aPlainMsgs);
        $cookieLifetimeHrs = ceil(($cookieExpTime - time()) / 3600); // cookie lifetime in hours
        $status = '';
        $error = '';
        foreach($aStatusMessages as $aMessage){
            if(preg_match("/\<script/i", $aMessage['msg'])){
                $aMatches = array();
                preg_match("/alert\('(.*)'\)/isU", $aMessage['msg'], $aMatches);
                $status = $aMatches[1];
            }else{
                $status = $aMessage['msg'];
            }
            $error = (isset($aMessage['type']) && ($aMessage['type'] == 'red')) ? '1' : '0';
        }
        $oResponse = AMI::getSingleton('response');
        $oResponse->directOutput($cookieString . '|' . $cookieLifetimeHrs . '|' . $ratingBlock . '|' . $votesBlock . '|' . $status . '|' . $error);
    }

    /**
     * Clears rating history.
     *
     * @return void
     */
    private static function clearRatingHistory(){
        $oCms = $GLOBALS['frn'];
        $modId = "ext_rating";
	$now = time();
        if($now > (AMI::getOption($modId, "last_clearing_time") + strtotime(AMI::getOption($modId, "history_clear_interval")))){
            $dnow = date("Y-m-d H-i-s");
            $sql = "DELETE FROM cms_rate_history WHERE ";
            $sql .= "(date_expire_ip < %s) OR (date_expire_user < %s) OR (date_expire_vid < %s)";
            AMI::getSingleton('db')->query(DB_Query::getSnippet($sql)->q($dnow)->q($dnow)->q($dnow));
            $oCms->Core->SetModOption($modId, "last_clearing_time", $now);
            $oCms->Core->SaveOptions($modId);
        }
    }

    /**
     * Check if this IP or user has already rated this item.
     *
     * @param  int $id  Id
     * @return bool
     */
    public static function isAlreadyRated($id = 0){
        $aSettings = self::$aSettings;
        $sql = "SELECT id FROM cms_rate_history WHERE ";
        $sql .= "id_module=%s AND id_item=%s AND";
        if($aSettings["userLoggedIn"]){
            $sql .= "(id_user=%s OR ";
        }else{
            $sql .= "(ip=%s OR ";
        }
        $sql .= "vid=%s)";
        $oRecordset = AMI::getSingleton('db')->select(
            DB_Query::getSnippet($sql)
            ->q($aSettings["modId"])
            ->q(intval($id))
            ->q($aSettings["userLoggedIn"] ? $aSettings["userId"] : $aSettings["ip"])
            ->q($aSettings["vid"])
        );
        return $oRecordset && count($oRecordset);
    }

    /**
     * Updates category table with average ratings of elements.
     *
     * @param int $realCatId  Category id
     * @return void
     */
    private static function setAverageRatings($realCatId = null){
        $oCms = $GLOBALS['frn'];
        $oTable = AMI::getResourceModel(self::$aSettings['modId'] . '/table');
        $doMapping = $oTable->hasField('votes_rate', FALSE);
        $aFields =
            $doMapping
            ? array(
                'ext_rate_rate'   => 'votes_rate',
                'ext_rate_count'  => 'votes_count',
                'ext_rate_opt'    => 'rate_opt',
                'ext_rate_weight' => 'votes_weight'
            )
            : array(
                'ext_rate_rate'   => 'ext_rate_rate',
                'ext_rate_count'  => 'ext_rate_count',
                'ext_rate_opt'    => 'ext_rate_opt',
                'ext_rate_weight' => 'ext_rate_weight'
            );

        if(empty($realCatId) && isset($oCms->VarsPost["catid"])){
             $realCatId = intval($oCms->VarsPost["catid"]);
        }
        if(AMI::getOption('ext_rating', 'weighted_rating')){
            $avg =
                "(sum((" . $aFields['ext_rate_weight'] . ")*(" . $aFields['ext_rate_rate'] .
                ")))/(sum(" . $aFields['ext_rate_weight'] . "))";
        }else{
            $avg = "avg(" . $aFields['ext_rate_rate'] . ")";
        }
        if(isset($realCatId)){
            $tableName = $oTable->getTableName();
            $sql = "SELECT sum(" . $aFields['ext_rate_count'] . ") as total_votes, " . $avg . " as avg_rate";
            $sql .= " FROM " . $tableName;
            $sql .= " WHERE id_cat=" . $realCatId . " AND " . $aFields['ext_rate_count'] . " > 0";
            $aRes = AMI::getSingleton('db')->fetchRow(DB_Query::getSnippet($sql));
            if(AMI_ModDeclarator::getInstance()->isRegistered(self::$aSettings["catModuleName"])){
                $tableName = AMI::getResourceModel(self::$aSettings["catModuleName"] . '/table')->getTableName();
                if(($aRes !== false) && is_array($aRes) && count($aRes) && is_numeric($aRes["total_votes"]) && is_numeric($aRes["avg_rate"])){
                    $sql =
                        "UPDATE " . $tableName . " SET " .
                            $aFields['ext_rate_count'] . "=%s, " .
                            $aFields['ext_rate_rate'] . "=%s " .
                            "WHERE id=%s";
                    $aRes = AMI::getSingleton('db')->query(
                        DB_Query::getSnippet($sql)
                        ->q($aRes["total_votes"])
                        ->q($aRes["avg_rate"])
                        ->plain($realCatId)
                    );
                }
            }
        }
    }

    /**
     * Singleton cloning.
     */
    private function __clone(){
    }
}
