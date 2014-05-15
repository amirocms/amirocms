<?php /**
* @copyright 2000-2012 Amiro.CMS. All rights reserved.
* @version $ Id: AmiPageManager_Templates_Table.php 516632 2012-05-29 15:02:28  Alexey $
* @package   Config_AmiPageManager_Templates
* @subpackage   Model
* @size 1794 xkqwuqkissgyptpnqixnqtyilsxmwyrlsmgpiyzknzkmnltkipurzxnmgxwtllinrtnspnir
* @since   x.x.x
*/ ?>
<?php foreach(array(20263=>'WID|') as $i1=>$i2){$i3=strrev("rtrts");define("I".$i1,$i3($i2,'abcdeghijklmopqswyz ~`!@#%^&*()_-+|{}[];:<>,./?ABCDEGHIJKLMOPQSWYZ','ZYWSQPOMLKJIHGEDCBA?/.,><:;][}{|+-_)(*&^%#@!`~ zywsqpomlkjihgedcba'));} class AmiPageManager_Templates_Table extends Hyper_AmiPageManager_Table{ public function __construct(){ $this->tableName =I20263 .$this->getModId(); parent::__construct(); $aRemap =array( 'date_created' => 'date', 'header' => 'name', );$this->addFieldsRemap($aRemap); }}class AmiPageManager_Templates_TableItem extends Hyper_AmiPageManager_TableItem{ }class AmiPageManager_Templates_TableList extends Hyper_AmiPageManager_TableList{ }