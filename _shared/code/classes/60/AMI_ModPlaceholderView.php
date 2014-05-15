<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModPlaceholderView.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     5.12.0
 */

/**
 * Module component view abstraction with placeholders support.
 *
 * Form and list views have placeholder support allowing flexible fields/columns manipulations.
 *
 * @package    ModuleComponent
 * @subpackage View
 * @since      5.12.0
 */
abstract class AMI_ModPlaceholderView extends AMI_View{
    /**
     * Elements template (placeholders)
     *
     * Can be redefined in child classes.<br /><br />
     *
     * Example:
     * <code>
     * // AMI_ModFormView
     * protected $aPlaceholders = array(
     *     '#form', // opening 'form' placeholder
     *         'public', 'id_page', 'id_cat', 'date_created', 'header', 'announce', 'body',
     *         '#seo', 'seo', // empty 'seo' placeholder
     *     'form' // closing 'form' placeholder
     * );
     * </code>
     *
     * @var array
     * @see AMI_ModPlaceholderView::addPlaceholders()
     * @see AMI_ModPlaceholderView::putPlaceholder()
     */
    protected $aPlaceholders = array();

    /**
     * Elemnts last inserted placeholder position
     *
     * @var    int
     * @amidev Temporary?
     */
    protected $elementLastPos;

    /**
     * Constructor.
     */
    public function __construct(){
        parent::__construct();

        $this->elementLastPos = sizeof($this->aPlaceholders) - 1;
    }

    /**
     * Add new field placeholders into placeholder list using AMI_ModPlaceholderView::putPlaceholder() without sections.
     *
     * Example:
     * <code>
     * // AmiSample_FilterView
     * public function __construct(){
     *     parent::__construct();
     *
     *     // Add admin filter form placeholder for 'nickname' before 'datefrom' filter field
     *     $this->addPlaceholders(array('nickname' => 'datefrom.before'));
     * }
     * </code>
     *
     * @param  array $aPlaceholders  Array of placeholders where keys are names and values are positions.
     *
     * @return AMI_ModPlaceholderView
     * @see    AMI_ModPlaceholderView::putPlaceholder()
     */
    public function addPlaceholders(array $aPlaceholders){
        foreach($aPlaceholders as $name => $positions){
            $this->putPlaceholder($name, $positions);
        }
        return $this;
    }

    /**
     * Returns placeholders.
     *
     * @param bool $bAssoc          Return as associative array
     * @param mixed $aPlaceholders  Placeholders array to use as a source, or false
     * @return boolean
     * @amidev Temporary?
     */
    public function getPlaceholders($bAssoc = false, $aPlaceholders = false){

        // Return placeholders as an associative array
        // @todo: a little bit ugly, maybe someone could optimize it?
        if($bAssoc){
            $aPlaceholdersAssoc = array();
            $aPlaceholdersOrigin = ($aPlaceholders === false) ? $this->aPlaceholders : $aPlaceholders;
            $bCanAdd = true;
            $processPlaceholder = '';
            foreach($aPlaceholdersOrigin as $placeholder){
                if($this->isSection($placeholder)){
                    if($bCanAdd && $this->isSectionStart($placeholder)){
                        $sectionName = $this->getCleanSectionName($placeholder);
                        $sectionStartPos = array_search($placeholder, $aPlaceholdersOrigin) + 1;
                        $sectionEndPos = array_search($sectionName, $aPlaceholdersOrigin);
                        $aSectionContent = array_slice($aPlaceholdersOrigin, $sectionStartPos, $sectionEndPos - $sectionStartPos);
                        $aPlaceholdersAssoc[$sectionName] = $this->getPlaceholders($bAssoc, $aSectionContent);
                        $bCanAdd = false;
                        $processPlaceholder = $sectionName;
                    }elseif(!$bCanAdd && ($placeholder == $processPlaceholder)){
                        $bCanAdd = true;
                        $processPlaceholder = '';
                    }
                }elseif($bCanAdd){
                    $aPlaceholdersAssoc[$placeholder] = true;
                }
            }
            return $aPlaceholdersAssoc;
        }

        // Everything except entity opening/closing placeholders
        // (i.e. '#form' and 'form')
        ### return array_slice($this->aPlaceholders, 1, -1);
        return $this->aPlaceholders;
    }

    /**
     * Checks if placeholder exists.
     *
     * @param  string $name  Placeholder name
     * @return bool
     * @amidev Temporary?
     */
    public function isPlaceholderExists($name){
        return in_array($name, $this->aPlaceholders);
    }

    /**
     * Checks if placeholder is a section.
     *
     * @param  string $name  Placeholder name
     * @return placeholder name or false
     * @amidev
     */
    public function isSection($name){
        $name = str_replace('#', '', $name);
        return ($this->isPlaceholderExists($name) && $this->isPlaceholderExists('#' . $name))?$name:false;
    }

    /**
     * Checks if placeholder is a section start.
     *
     * @param  string $name  Placeholder name
     * @return placeholder name or false
     * @amidev Temporary?
     */
    public function isSectionStart($name){
        $isSection = $this->isSection($name);
        return ($isSection && strpos($name, '#') === 0 )?$isSection:false;
    }

    /**
     * Checks if placeholder is a section end.
     *
     * @param  string $name  Placeholder name
     * @return placeholder name or false
     * @amidev Temporary?
     */
    public function isSectionEnd($name){
        $isSection = $this->isSection($name);
        return ($isSection && strpos($name, '#') !== 0 )?$isSection:false;
    }

    /**
     * Get clean section name without '#'.
     *
     * @param  string $name  Placeholder name
     * @return placeholder name
     * @amidev Temporary?
     */
    public function getCleanSectionName($name){
        return str_replace('#', '', $name);
    }

    /**
     * Puts placeholder into required position.
     *
     * Array of placeholders where keys are field names and values are positions.<br /><br />
     *
     * Example:
     * <code>
     * // AmiSample_FilterView
     * public function __construct(){
     *     parent::__construct();
     *
     *     // Add admin filter form placeholder for 'nickname' before 'datefrom' filter field
     *     $this->putPlaceholder('nickname', 'datefrom.before');
     * }
     * </code>
     *
     * @param  string $name       Placeholder name (same as field name)
     * @param  string $positions  Placeholder positions in format {placeholder[.position][|placeholder[.position]|[...]]}, where:<br />
     *                            - placeholder - existing placeholder;
     *                            - position - begin|end|before|after, after by default, order explanation using AMI_ModPlaceholderView::$aPlaceholders:
     *                              seo.before < seo.begin < seo.end < seo.after
     *                            Each placeholder will search for positions separated by '|' and will be placed at first exiting position.<br /><br />
     * @param  bool $isSection    Section flag, if passed true secotion will be created
     * @return bool|int           False if placeholder exists otherwise index where the placeholder was put
     * @see    AMI_ModPlaceholderView::addPlaceholders()
     */
    public function putPlaceholder($name, $positions = '', $isSection = false){
        // Index where item to be set
        $index = false;
        if($this->isPlaceholderExists($name)){
            return $index;
        }
        if($positions != ''){
            $index = $this->locatePlaceholder($name, $positions);
        }else{
            // Put into last position
            $index = $this->elementLastPos;
        }
        if($index !== false){
            array_splice($this->aPlaceholders, $index, 0, $isSection ? array('#' . $name, $name): array($name));
            $this->elementLastPos = $index + ($isSection ? 2 : 1);
        }else{
            trigger_error('No location found for placeholder "' . $name . '", positions: "' . $positions . '"', E_USER_ERROR);
        }
        return $index;
    }

    /**
     * Drops placeholder.
     *
     * @param array $aPlaceholders  Placeholder names to delete
     * @return AMI_ModPlaceholderView
     * @amidev Temporary?
     */
    public function dropPlaceholders(array $aPlaceholders){
        foreach($aPlaceholders as $name){
            if($this->isPlaceholderExists($name)){
                // Remove a section with all inner placeholders
                if($this->isSection($name)){
                    $indexStart = array_search('#' . $name, $this->aPlaceholders);
                    $indexEnd = array_search($name, $this->aPlaceholders);
                    array_splice($this->aPlaceholders, $indexStart, $indexEnd - $indexStart + 1);
                }else{
                    unset($this->aPlaceholders[array_search($name, $this->aPlaceholders)]);
                }
                $this->aPlaceholders = array_values($this->aPlaceholders);
            }
        }
        return $this;
    }

    /**
     * Locates placeholder position.
     *
     * @param  string $name       Placeholder name
     * @param  string $positions  Placeholder positions
     * @return bool|int           False if position isn't located or located position otherwise
     * @amidev
     */
    protected function locatePlaceholder($name, $positions){
        // Index where item to be set
        $index = false;
        foreach(explode('|', $positions) as $pos){
            list($placeholder, $relPos) = explode('.', $pos);
            if(!in_array($relPos, array('begin', 'end', 'before', 'after'))){
                trigger_error('Invalid relative position "' . $relPos . '" for place "' . $name . '" in position "' . $pos . '"', E_USER_ERROR);
            }
            $placeholderPos = array_search($placeholder, $this->aPlaceholders);
            if($placeholderPos !== false){
                // Make pos modifier
                $posModifier = ($relPos == 'after' || $relPos == 'begin') ? 1 : 0;
                $count = sizeof($this->aPlaceholders);
                if($index && $placeholder == $this->aPlaceholders[$count - 1] && $relPos != 'begin' && $relPos != 'end'){
                    trigger_error('Invalid position "' . $relPos . '" for place "' . $name . '" in positions "' . $positions . '"', E_USER_ERROR);
                }
                if($relPos == 'begin' || $relPos == 'before'){
                    // Check if section exists, try to add before section
                    $sectionPos = array_search('#' . $placeholder, $this->aPlaceholders);
                    if($sectionPos !== false){
                        $placeholderPos = $sectionPos;
                    }
                }
                $index = $placeholderPos + $posModifier;
                break;
            }
        }
        return $index;
    }
}
