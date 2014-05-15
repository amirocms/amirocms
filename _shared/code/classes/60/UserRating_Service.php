<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   AmiExt_UserRating
 * @version   $Id: UserRating_Service.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * User rating service functions.
 *
 * @package    AmiExt_UserRating
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class UserRating_Service extends AMI_Module_Service{
    /**
     * Dispatches user rate action.
     *
     * @param AMI_Request  $oRequest   Request
     * @param AMI_Response $oResponse  Response
     * @return void
     */
    public function dispatchAction(AMI_Request $oRequest, AMI_Response $oResponse){
        if(!AMI::isModInstalled('ext_user_rating')){
            $oResponse->HTTP->setServiceUnavailable(3600);
            $oResponse->send();
        }

        $aAllowedRatings = AMI::getOption('ext_user_rating', 'allow_negative_rating') ? array(-1, 1) : array(1);
        $oUser = AMI::getSingleton('env/session')->getUserData();
        $myId = ($oUser) ? $oUser->getId() : null;
        $oUser = AMI::getResourceModel('users/table')->find($myId, array('id', 'rating_value'));

        $originalModId = $oRequest->get('module_id', '');
        $locale = $oRequest->get('locale', 'en');
        AMI_Registry::set('lang', $locale);
        $modId  = ($originalModId == 'forum') ? 'forum' : 'discussion';

        $aExtensions = AMI::getOption($modId, 'extensions');
        if(!in_array('ext_user_rating', $aExtensions)){
            // Do nothing
            return;
        }

        $itemId = (int)$oRequest->get('id');
        $userId = (int)$oRequest->get('user_id');
        $rating = (int)$oRequest->get('rating');
        $result = array(
            'status'        => 0,
            'status_msg'    => ''
        );
        $oTpl = AMI::getResource('env/template_sys');
        $aLocale = $oTpl->parseLocale('templates/lang/user_rating.lng');

        $oItem = null;
        if($modId && AMI::isModInstalled($modId)){
            $oItem = AMI::getResourceModel($modId . '/table')->find($itemId, array('id', 'id_member', 'rating_pos', 'rating_neg'));
        }
        $inputOk = false;
        if($myId && ($oUser->rating_value >= 0) && !is_null($oItem) && $oItem->id && $userId && in_array($rating, $aAllowedRatings) && ($myId != $userId) && ($oItem->id_member == $userId)){
            $inputOk = true;
        }
        if($inputOk){
            AMI_Registry::set('ami_allow_model_save', true);
            $oRatingHistory = AMI::getResourceModel('users/rating/table')->getItem();
            $oRatingHistory->addFields(array('id', 'date_created'))->loadByFields(
                array(
                    'id_voter'  => $myId,
                    'id_module' => $modId,
                    'id_item'   => $itemId
                )
            );

            if(!$oRatingHistory->id){
                $oRatingHistory->id_voter   = $myId;
                $oRatingHistory->id_member  = $userId;
                $oRatingHistory->id_module  = $modId;
                $oRatingHistory->id_item    = $itemId;
                $oRatingHistory->rate_value = $rating;

                $oRatingHistory->date_created   = DB_Query::getSnippet('NOW()');
                $oRatingHistory->save();
                if($oRatingHistory->id){
                    $oRating = AMI::getSingleton('users/rating');
                    if($rating > 0){
                        $oRating->incRating($userId);
                        $oItem->rating_pos++;
                        $oItem->save();
                    }else{
                        $oRating->decRating($userId);
                        $oItem->rating_neg++;
                        $oItem->save();
                    }

                    if(1 || ($GLOBALS['ROOT_PATH_WWW'] === 'http://www.amiro.ru/')){
                        $oRatingHistory->rate_vote = $rating;
                        $oRatingHistory->rate_value = $rating * $oRating->getRatedValue();
                        $oRatingHistory->save();
                    }

                    $GLOBALS['oCache']->ClearAdd($originalModId);
                    $result['status'] = 1;
                    $result['status_msg'] = $aLocale['rate_ok'];
                    $result['user_id'] = $userId;
                    $result['item_id'] = $itemId;
                    $result['rating'] = $rating;
                    $result['user_rating_html'] = $oRating->getUserRatingHTML($userId);
                    $result['item_rating_html'] = $oRating->getItemRatingHTML($oItem);
                }
            }else{
                $date = AMI_Lib_Date::formatDateTime($oRatingHistory->date_created, AMI_Lib_Date::FMT_BOTH);
                $result['status_msg'] = mb_ereg_replace('_date_', AMI_Lib_String::htmlChars($date), $aLocale['already_voted']);
            }
        }else{
            $result['status_msg'] = $aLocale['invalid_params'];
        }
        $oResponse->setType('JSON');
        $oResponse->write($result);
    }
}
