<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   AmiExt_UserRating
 * @version   $Id: AMI_UserRating.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * User rating.
 *
 * @package   AmiExt_UserRating
 * @since     x.x.x
 * @amidev    Temporary
 */
class AMI_UserRating{

    /**
     * Current user
     *
     * @var object
     */
    protected $currentUser = null;

    /**
     * User rating template
     *
     * @var string
     */
    protected $template = 'templates/user_rating.tpl';

    /**
     * Templator engine
     *
     * @var object
     */
    protected $oTpl = null;

    /**
     * Rated value
     *
     * @var float
     */
    protected $ratedValue = 0;

    /**
     * Constructor.
     */
    public function __construct(){
        $oUser = AMI::getSingleton('env/session')->getUserData();
        $userId = ($oUser) ? $oUser->getId() : null;
        if($userId){
            $this->currentUser = AMI::getResourceModel('users/table')->find($userId, array('id', 'rating_value'));
            $oSession = isset($GLOBALS['oSession']) && is_object($GLOBALS['oSession']) ? $GLOBALS['oSession'] : new CMS_Session($GLOBALS['cms'], AMI_Registry::get('lang'));
            $oSession->addJsSessionCookie('rating', $this->currentUser->rating_value);
            $oSession->addJsSessionCookie('id_cookie', $userId);
            $oSession->storeJsSessionCookies();
        }
        $this->oTpl = AMI::getResource('env/template_sys');
        $this->oTpl->addBlock('user_rating', $this->template);
    }

    /**
     * Increase user rating.
     *
     * @param  string $userId  User ID
     * @return void
     */
    public function incRating($userId){
        if(!is_null($this->currentUser)){
            $oMember = AMI::getResourceModel('users/table')->find($userId, array('id', 'rating_value', 'rating_count'));
            if($oMember->id){
                $oMember->rating_value += $this->_calculateStep($this->currentUser, $oMember);
                $this->_updateStats($userId, $oMember->rating_value, $oMember->rating_count + 1);
            }
        }
    }

    /**
     * Decrease user rating.
     *
     * @param  string $userId  User's ID
     * @return void
     */
    public function decRating($userId){
        if(!is_null($this->currentUser)){
            $oMember = AMI::getResourceModel('users/table')->find($userId, array('id', 'rating_value', 'rating_count'));
            if($oMember->id){
                $oMember->rating_value -= $this->_calculateStep($this->currentUser, $oMember);
                $this->_updateStats($userId, $oMember->rating_value, $oMember->rating_count + 1);
            }
        }
    }

    /**
     * Get HTML for user rating form.
     *
     * @param  int  $userId  User ID
     * @param  bool $full    Returns rating html weapped with div container
     * @param  int  $rating  User rating (optional, default null)
     * @return string
     */
    public function getUserRatingHTML($userId, $full = FALSE, $rating = null){
        if(!AMI_Registry::exists('amiRatingMinMaxValues')){
            $aResult =
                AMI::getSingleton('db')
                ->fetchRow(
                    DB_Query::getSnippet('SELECT MAX(rating_value) as maxval, MIN(rating_value) as minval FROM cms_members')
                );
            AMI_Registry::set('amiRatingMinMaxValues', $aResult);
        }else{
            $aResult = AMI_Registry::get('amiRatingMinMaxValues', array('maxval' => 0, 'minval' => 0));
        }
        $maxValue = $aResult['maxval'];
        $minValue = $aResult['minval'];
        if(is_null($rating)){
            $oUser = AMI::getResourceModel('users/table')->find($userId, array('id', 'rating_value'));
            $userVal = $oUser->rating_value;
        }else{
            $userVal = $rating;
        }

        $result = 0;
        if(($minValue > 0) && ($userVal < 0)){
            $result -100;
        }
        if(($maxValue < 0) && ($userVal > 0)){
            $result = 100;
        }
        if(($minValue < 0) && ($userVal < 0)){
            $result = -round(($userVal/$minValue)*100);
        }
        if(($maxValue > 0) && ($userVal > 0)){
            $result = round(($userVal/$maxValue)*100);
        }
        if($result < -100){
            $result = -100;
        }
        if($result > 100){
            $result = 100;
        }

        $aScope = array(
            'value'         => $result,
            'stars'         => ceil($result/20),
            'abs_value'     => abs($result),
            'real_value'    => $userVal,
            'neg_allowed'   => AMI::getOption('ext_user_rating', 'allow_negative_rating')
        );
        $html = $this->oTpl->parse('user_rating:member', $aScope);
        if($full){
            $aScope = array(
                'user_id'     => $userId,
                'rating_html' => $html
            );
            $html =  $this->oTpl->parse('user_rating:user_rating', $aScope);
        }
        return $html;
    }

    /**
     * Gets HTML for item rating form.
     *
     * @param  AMI_ModTableItem $oItem  User rating history item
     * @param  bool $full               Returns rating html wrapped with div container if true
     * @return string
     */
    public function getItemRatingHTML(AMI_ModTableItem $oItem, $full = FALSE){
        $aScope = array(
            'positive_count' => $oItem->rating_pos,
            'negative_count' => $oItem->rating_neg,
            'total_count'    => $oItem->rating_pos + $oItem->rating_neg,
            'rating'         => $oItem->rating_pos - $oItem->rating_neg
        );
        $html = $this->oTpl->parse('user_rating:item', $aScope);
        if($full){
            $aScope = array(
                'user_id'     => $oItem->id_member,
                'item_id'     => $oItem->id,
                'neg_allowed' => AMI::getOption('ext_user_rating', 'allow_negative_rating'),
                'rating_html' => $html
            );
            $html = $this->oTpl->parse('user_rating:item_rating', $aScope);
        }
        return $html;
    }

    /**
     * Returns last rated value.
     *
     * @return float
     */
    public function getRatedValue(){
        return $this->ratedValue;
    }

    /**
     * Update user rating.
     *
     * @param  int $id     User ID
     * @param  int $value  Rating value
     * @param  int $count  Rating counter
     * @return void
     */
    protected function _updateStats($id, $value, $count){
        AMI::getSingleton('db')->query(
            DB_Query::getSnippet('UPDATE `cms_members` SET `rating_value` = %s, `rating_count` = %s WHERE `id` = %s')
            ->q($value)
            ->q($count)
            ->q($id)
        );
    }

    /**
     * Calculate reputation increase step.
     *
     * @param AmiUsers_Users_TableItem $oVoter   Voter user object
     * @param AmiUsers_Users_TableItem $oMember  Member user object
     * @return float
     */
    protected function _calculateStep(AmiUsers_Users_TableItem $oVoter, AmiUsers_Users_TableItem $oMember){
        if(!AMI::getOption('ext_user_rating', 'weighted_rating')){
            $result = 1;
        }else{ // option
            $voterRating = abs($oVoter->rating_value);
            $memberRating = abs($oMember->rating_value);

            // LOG(B$34+2;10)*$A35/10+1+($A35*100/($A35*5+10)*(-1/(B$34/100+1)+1)).
            // $A35 - voter reputation, а B$34 - member_reputation.
            $count = (int)AMI::getSingleton('db')->fetchValue(
                DB_Query::getSnippet(
                    'SELECT COUNT(1) FROM `cms_members_rating_history` ' .
                    'WHERE `id_member` = %s AND `id_voter` = %s AND `date_created` > DATE_SUB(NOW(), INTERVAL 1 MONTH)'
                )
                ->q($oMember->id)
                ->q($oVoter->id)
            );

            if(!$count){
                $count = 1;
            }

            $result = log($memberRating + 2, 10) * $voterRating / 10 + 1 + ($voterRating * 100 / ($voterRating * 5 + 10) * (-1 / ($memberRating / 100 + 1) + 1));
            $result = round($result / ($count * $count), 2);
        }
        $this->ratedValue = $result;
        return $result;
    }
}
