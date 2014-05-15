<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModListGroupActionsView.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * List group action view.
 *
 * @package    ModuleComponent
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
class AMI_ModListGroupActionsView extends AMI_ModPlaceholderView{

    /**
     * Puts a placeholder, moves current element index to the end of the list.
     *
     * @param  string $name       Placeholder name (same as field name)
     * @param  string $positions  Placeholder positions in format {placeholder[.position][|placeholder[.position]|[...]]}, where:<br />
     *                            - placeholder - existing placeholder;
     *                            - position - begin|end|before|after, after by default, order explanation using AMI_ModPlaceholderView::$aPlaceholders:
     *                              seo.before < seo.begin < seo.end < seo.after
     *                            Each placeholder will search for positions separated by '|' and will be placed at first exiting position.<br /><br />
     * @param  bool $isSection    Section flag, if passed true secotion will be created
     * @return bool|int           False if placeholder exists otherwise index where the placeholder was put
     * @see    AMI_ModPlaceholderView::putPlaceholder()
     */
    public function putPlaceholder($name, $positions, $isSection = false){
        $index = parent::putPlaceholder($name, $positions, $isSection);
        $this->elementLastPos = sizeof($this->aPlaceholders);
        return $index;
    }

    /**
     * Returns view data.
     *
     * @return array
     */
    public function get(){
        $aGroupActions = array();
        $isFirstSection = true;
        $sectionCounter = 0;
        foreach($this->aPlaceholders as $placeholder){
            if(0 === mb_strpos($placeholder, '#')){
                if($isFirstSection){
                    $isFirstSection = false;
                }else{
                    if($sectionCounter > 0){
                        $aGroupActions[] = 'grp_|';
                    }
                }
                $sectionCounter = 0;
            }elseif(!$this->isSection($placeholder)){
                $sectionCounter++;
                $aGroupActions[] = 'grp_' . $placeholder;
            }
        }
        return $aGroupActions;
    }
}
