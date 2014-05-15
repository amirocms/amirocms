<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Library
 * @version   $Id: AMI_Lib_BB.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * BB functionality.
 *
 * @package Library
 * @since   x.x.x
 * @amidev  Temporary
 */
class AMI_Lib_BB{
    /**
     * Converts BB to HTML.
     *
     * @param  string $content        Content
     * @param  bool   $isHTMLEncoded  HTML encoded flag
     * @param  bool   $useNoIndex     Noindex flag
     * @param  array  $aURLParams     Extra url params
     * @return string
     */
    public function toHTML($content, $isHTMLEncoded = true, $useNoIndex = false, array $aURLParams = array()){
        // Get smile paths
        $smilesFolder = 'base';
        if(is_file($GLOBALS["ROOT_PATH"].'_mod_files/smiles/current.dat')){
            $fileConfig = trim(file_get_contents($GLOBALS["ROOT_PATH"].'_mod_files/smiles/current.dat'));
            if(!empty($fileConfig) && is_dir($GLOBALS["ROOT_PATH"].'_mod_files/smiles/'.$fileConfig)){
                $smilesFolder = $fileConfig;
            }
        }
        $smilesPath = $GLOBALS["ROOT_PATH"]."_mod_files/smiles/".$smilesFolder."/";
        $smilesPathWWW = "_mod_files/smiles/".$smilesFolder."/";
        // Get smile names and define special smiles
        $smileNames = array();
        if(is_file($GLOBALS["ROOT_PATH"].'_local/config_editor.php')){
            $configData = parse_ini_file($GLOBALS["ROOT_PATH"].'_local/config_editor.php', true);
            foreach(array('BASE_SMILES', 'EXTRA_SMILES') as $dataType){
                foreach($configData[$dataType] as $smileName => $smileValue){
                    $smileName = trim($smileName);
                    $smileValue = trim($smileValue);
                    if(strncmp($smileName, $smilesFolder, mb_strlen($smilesFolder)) == 0){
                        $smileName = mb_substr($smileName, mb_strlen($smilesFolder)+1);
                        $smileNames[$smileName] = $smileValue;
                    }
                }
            }
        }
        $specSmiles = array(";)" => "wink", ":)" => "smile", ":D" => "laugh", ":(" => "frown");
        // Prepare content
        if(!$isHTMLEncoded){
            $content = htmlspecialchars($content);
        }
        $content = preg_replace('/\r?\n|\r\n?/s', "<br>\r\n", $content);
        // Do replacements
        $rxPatterns = array(
            '/\[CODE\](.*?)\[\/CODE\]/ies',
            '/\[(\/?)(B|I|U|S|P)\]/i',
            '/\[Q\]/i',
            '/\[Q=&quot;(.*?)&quot;\]/i',
            '/\[(\/?)(INDENT|Q)\]/i',
            '/\[(LEFT|RIGHT|CENTER|JUSTIFY)\]/i',
            '/\[\/(LEFT|RIGHT|CENTER|JUSTIFY)\]/i',
            '/\[H(\d)\]/i',
            '/\[\/H(\d)\]/i',
            '/\[LIST\]/i',
            '/\[\/LIST\]/i',
            '/\[OL\]/i',
            '/\[\/OL\]/i',
            '/\[\*\]/i',
            '/\[FONT=&quot;(.*?)&quot;\]/ie',
            '/\[(COLOR|SIZE)=&quot;(.*?)&quot;\]/ie',
            '/\[\/(FONT|SIZE|COLOR)\]/i',
            '/\[URL=&quot;(.*?)&quot;\](.*?)\[\/URL\]/ies',
            '/\[IMG\](.*?)\[\/IMG\]/sie',
            '/(;\)|:\)|:D|:\()/e',
            '/\:([a-z\_]{1,20})\:/ie',
            '/(<IMG .*?>|<A .*?<\/A>)/ise',
            '/(?:https?:\/\/www\.|https?:\/\/|www\.)(?:[a-z0-9\-]+\.)+[a-z]{2,8}[^ \r\n\t\'"><]*/ie',
            '/(<IMG .*?>|<A .*?<\/A>)/ise'
        );
        $rxReplacements = array(
            'self::_getCodeContent(\'$1\')',
            '<\1\2>',
            '<BLOCKQUOTE class="edQuote">',
            '<BLOCKQUOTE class="edQuote"><b>\1:</b><br>',
            '<\1BLOCKQUOTE>',
            '<DIV class="edParagraph" style="text-align:\1;">',
            '</DIV>',
            '<H\1>',
            '</H\1>',
            '<UL>',
            '</LI></UL>',
            '<OL>',
            '</LI></OL>',
            '<LI>',
            '"<FONT face=\"".self::_tagsParamReplace(\'$1\', false)."\">"',
            '"<FONT ".$1."=\"".self::_tagsParamReplace(\'$2\', false)."\">"',
            '</FONT>',
            'self::_getLinkContent(\'$1\', \'$2\', $useNoIndex, \$aURLParams)',
            '"<IMG SRC=\"".self::_tagsParamReplace(\'$1\', true)."\">"',
            '"<IMG SRC=\"".$smilesPathWWW.$specSmiles[\'$1\'].".gif\" title=\"".self::_getSmileName($smileNames, $specSmiles[\'$1\'])."\">"',
            'is_file($smilesPath."$1.gif") ? "<IMG SRC=\"".$smilesPathWWW."$1.gif\" title=\"".self::_getSmileName($smileNames, "$1")."\">" : ":$1:"',
            'preg_replace(array ("/www/si", "/http/si"), array ("wBREAKwBREAKw", "htBREAKtp"), self::_pregStripSlashes(\'$1\'))',
            'self::_getLinkContent(\'$0\', \'$0\', $useNoIndex, \$aURLParams)',
            'preg_replace(array ("/htBREAKtp/si", "/wBREAKwBREAKw/si"), array ("http", "www"), self::_pregStripSlashes(\'$1\'))',
        );
        $GLOBALS['codeContentStorage'] = array();
        $content = preg_replace($rxPatterns, $rxReplacements, $content);
        for($i = 0; $i < sizeof($GLOBALS['codeContentStorage']); $i++){
            $content = preg_replace('/(<PRE[^>]*?>)CodeId\_'.$i.'(<\/PRE>)/', '\1'.$GLOBALS["codeContentStorage"][$i].'\2', $content);
        }
        $aCheckTags = array('b','i','u','li','font','a','pre','blockquote','p','ul');
        for($i = 0; $i < sizeof($aCheckTags); $i++){
            $countOpened = preg_match('/<' . $aCheckTags[$i] . '( |>)/si', $content);
            $countClosed = preg_match('/<\/' . $aCheckTags[$i] . '( |>)/si', $content);
            if($countClosed < $countOpened){
                $content .= str_repeat('</' . $aCheckTags[$i] . '>', $countOpened - $countClosed);
            }
        }
        return $content;
    }

    /**
     * Add description later.
     *
     * @param array $smileNames  Smile names
     * @param string $smileName  Smile name
     * @return string
     */
    private function _getSmileName(array $smileNames, $smileName){
        if(isset($smileNames[$smileName])){
            return $smileNames[$smileName];
        }
        return '';
    }

    /**
     * Add description later.
     *
     * @param string $content  Content
     * @return string
     */
    private function _pregStripSlashes($content){
        return str_replace(array('\"'), array('"'), $content);
    }

    /**
     * No description.
     *
     * @param  string $content       Content
     * @param  bool   $doCheckImage  Do check image
     * @return string
     * @todo   Add description
     */
    private function _tagsParamReplace($content, $doCheckImage){
        $content = self::_pregStripSlashes($content);
        $content = str_replace('&amp;', '&', $content);

        do{
            $prevLength = mb_strlen($content);
            $content = preg_replace('/^\s*javascript\:/', '', $content);
        }while(mb_strlen($content) != $prevLength);

        if($doCheckImage){
            $test = mb_strtolower(preg_replace('/^ *(.*?) *$/', '$1', $content));
            if(mb_strpos($test, '/_admin/') !== false){
                $content = '';
            }else{
                $isRelativeAddress = !preg_match('/^https?:\/\//', $test);
                $isLocalAddress = false;
                if(!$isRelativeAddress){
                    $test = preg_replace('/^https:\/\//', 'http://', $test);
                    if(mb_strpos($test, $GLOBALS["ROOT_PATH_WWW"]) !== false){
                        $isLocalAddress = true;
                    }
                }
                if(($isRelativeAddress || $isLocalAddress) && !preg_match('/^[^\?]*\.(jpg|png|gif|jpeg|swf)$/', $test)){
                    $content = '';
                }
            }
        }

        return $content;
    }

    /**
     * Get link content.
     *
     * @param  string $url         URL
     * @param  string $content     Content
     * @param  bool   $useNoIndex  Use no index
     * @param  array  $aURLParams  URL params
     * @return string
     */
    public function _getLinkContent($url, $content, $useNoIndex, array $aURLParams){
        $url = self::_pregStripSlashes($url);
        $realUrl = $url;
        if(preg_match('/^data\:/si', $url)){
            $realUrl = '';
        }
        if(preg_match('/^www\./si', $url)){
            $realUrl = 'http://'.$realUrl;
        }
        $content = self::_pregStripSlashes(trim($content));
        $attributes = '';
        if(isset($aURLParams[0]) && $aURLParams[0] > 0){
            $preparedContent = strip_tags(html_entity_decode($content, ENT_QUOTES, $aURLParams[1]));
            if(mb_strlen($preparedContent) > $aURLParams[0]){
                $attributes = ' title="' . str_replace('"', '&quot;', $preparedContent) . '"';
                $content = htmlspecialchars(mb_substr($preparedContent, 0, $aURLParams[0] - 8 - 3) . '...' . mb_substr($preparedContent, -8));
                $content = str_replace(array ('#', '%', '\''), array ('&#035;', '&#037;', '&#039;'), $content);
            }
        }
        $realUrl = AMI_Lib_BB::_tagsParamReplace($realUrl, false);
        if($useNoIndex && (!preg_match('/^https?:\/\//i', $realUrl) || (mb_strpos($realUrl, $GLOBALS["ROOT_PATH_WWW"]) !== false)))
            $useNoIndex = false;
        return ($useNoIndex ? '<NOINDEX>' : '').'<A ' . $attributes . ' HREF="'.$realUrl.'"'.($useNoIndex ? ' rel="nofollow"' : '').' target="_blank">'.$content.'</A>'.($useNoIndex ? '</NOINDEX>' : '');
    }

    /**
     * Get code content.
     *
     * @param string $content  Content
     * @return string
     */
    public function _getCodeContent($content){
        $content = self::_pregStripSlashes($content);
        $content = preg_replace('/<br>/si', '', $content);
        $nlCount = 1;
        $nlPos = mb_strpos($content, "\n");
        while($nlPos !== false){
            $nlCount++;
            $nlPos = mb_strpos($content, "\n", $nlPos+1);
        }
        if($nlCount > 10)
            $nlCount = 10;
        $codeKey = sizeof($GLOBALS['codeContentStorage']);
        $GLOBALS['codeContentStorage'][$codeKey] = $content;
        return '<PRE class="edCode" style="height:'.($nlCount*15+40).'">CodeId_'.$codeKey.'</PRE>';
    }

    /**
     * Converts smiles to BB-tags.
     *
     * @param  string $html  HTML data to process
     * @return string
     */
    public static function convertSmilesToTags($html){
        $smilesFolder = 'base';
        $configParh = $GLOBALS['ROOT_PATH'] . '_mod_files/smiles/current.dat';
        if(is_file($configParh)){
            $fileConfig = trim(file_get_contents($configParh));
            if(!empty($fileConfig) && is_dir($GLOBALS['ROOT_PATH'] . '_mod_files/smiles/' . $fileConfig)){
                $smilesFolder = $fileConfig;
            }
        }
        $smilesPathWWW = '_mod_files/smiles/' . $smilesFolder . '/';
        $specSmiles = array(
            '"wink.gif"'  => '";).gif"',
            '"smile.gif"' => '":).gif"',
            '"laugh.gif"' => '":D.gif"',
            '"frown.gif"' => '":(.gif"'
        );
        return
            preg_replace(
                array(
                    '/' . preg_quote('<IMG SRC="' . $smilesPathWWW, '/') . '([^\.]+?)' . preg_quote('.gif"', '/') . '([^>]*)?\>/si',
                    '/\:\;\)\:/',
                    '/\:\:\)\:/',
                    '/\:\:\D\:/',
                    '/\:\:\(\:/'
                ),
                array(
                    ':\\1:',
                    ';)',
                    ':)',
                    ':D',
                    ':('
                ),
                str_replace(array_keys($specSmiles), $specSmiles, $html)
            );
    }
}
