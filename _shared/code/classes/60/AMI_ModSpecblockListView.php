<?php /**
* @copyright 2000-2012 Amiro.CMS. All rights reserved.
* @version $ Id: AMI_ModSpecblockListView.php 518539 2012-09-13 22:11:33  Anton $
* @package   ModuleComponent
* @subpackage   View
* @size 845 xkqwtgzsszpnxsngsmtnungqglunxrlrpstkzuqgwylyypxlmkrnyilixpsttxxxqptmpnir
*/ ?>
<?php foreach(array(15122=>'tQIGJZtQD~60~') as $i1=>$i2){$i3=strrev("rtrts");define("I".$i1,$i3($i2,'abcdeghijklmopqswyz ~`!@#%^&*()_-+|{}[];:<>,./?ABCDEGHIJKLMOPQSWYZ','ZYWSQPOMLKJIHGEDCBA?/.,><:;][}{|+-_)(*&^%#@!`~ zywsqpomlkjihgedcba'));} abstract class AMI_ModSpecblockListView extends AMI_ModListView{ public function __construct(){ $this->tplFileName =I15122 .$this->getModId() .'_specblock.tpl'; $this->tplBlockName =$this->getModId() .'_specblock'; $this->localeFileName ='templates/lang/60/' .$this->getModId() .'_specblock.lng'; parent::__construct(); }}