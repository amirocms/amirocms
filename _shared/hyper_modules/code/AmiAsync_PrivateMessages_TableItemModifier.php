<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   AmiAsync/PrivateMessages
 * @version   $Id: AmiAsync_PrivateMessages_TableItemModifier.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Private messages table item model modifier.
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage Model
 * @resource   private_messages/table/model/item/modifier <code>AMI::getResourceModel('private_messages/table')->getItem()->getModifier()</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAsync_PrivateMessages_TableItemModifier extends Hyper_AmiAsync_TableItemModifier{
    /**
     * Returns default fields and its values on save.
     *
     * @param  bool $onCreate  True on item create, false on update
     * @return array           Array having keys as field names and values as field values
     * @see    AMI_ModTableItemModifier::getDefaultsOnSave()
     */
    public function getDefaultsOnSave($onCreate){
        $aResult = parent::getDefaultsOnSave($onCreate);
        if(is_null($this->oTableItem->id_body)){
            /**
             * @var PrivateMessageBodies_Table
             */
            $oBodyTable = AMI::getResourceModel('private_message_bodies/table');
            $body = $this->oTableItem->b_body;
            if(!$this->oTableItem->is_broadcast){
                $body = str_replace("&quot;", '"', $body);
                $body = html_entity_decode(AMI_Lib_BB::toHTML($body, false));
            }
            if($onCreate){
                $oBodyItem = $oBodyTable->getItem();
                $oBodyItem->body = $body;
                $oBodyItem->save();
                $aResult['append']['id_body'] = $oBodyItem->getId();
                $this->oTableItem->id_body = $aResult['append']['id_body'];
            }else{
                $oBodyItem = $oBodyTable->find($this->oTableItem->id_body);
                if($oBodyItem->body !== $body){
                    $oBodyItem->body = $body;
                    $oBodyItem->save();
                }
            }
        }
        return $aResult;
    }

    /**
     * Calls when validation failed.
     *
     * @param  array $aData     Item data
     * @param  bool  $onCreate  True on item create, false on update
     * @return void
     */
    protected function rollback(array $aData, $onCreate){
        parent::rollback($aData, $onCreate);
        if($onCreate && $aData['id_body']){
            AMI::getResourceModel('private_message_bodies/table')->getItem()->delete($aData['id_body']);
        }
    }

    /**
     * Deletes item from table.
     *
     * @param  mixed $id  Primary key value of item
     * @return bool       True if any record were deleted
     * @see    AMI_ModTableItemModifier::delete()
     */
    public function delete($id = null){
        if(is_null($id)){
            $id = $this->oTableItem->getId();
        }else{
            if($this->oTableItem->getId() != $id){
                $this->oTableItem->addSearchCondition(array('id' => $id))->load();
            }
        }
        $bodyId = $this->oTableItem->id_body;
        $result = parent::delete($id);
        if($result && $bodyId){
            // Delete body if there are no references
            $oList =
                $this->oTable->getList()
                    ->addColumn('id')
                    ->addWhereDef('AND `id_body` = ' . $bodyId)
                    ->setLimitParameters(0, 1)
                    ->load();
            if(!sizeof($oList)){
                AMI::getResourceModel('private_message_bodies/table')->getItem()->delete($bodyId);
            }
        }
        return $result;
    }
}

/**
 * Private message body table item model modifier.
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage Model
 * @resource   private_message_bodies/table/model/item/modifier <code>AMI::getResourceModel('private_message_bodies/table')->getItem()->getModifier()</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class PrivateMessageBodies_TableItemModifier extends Hyper_AmiAsync_TableItemModifier{
}

/**
 * AmiAsync/PrivateMessages configuration contacts table item modifier.
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage Model
 * @resource   private_message_contacts/table/model/item/modifier <code>AMI::getResourceModel('private_message_contacts/table')->getItem()->getModifier()</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class PrivateMessageContacts_TableItemModifier extends Hyper_AmiAsync_TableItemModifier{
}
