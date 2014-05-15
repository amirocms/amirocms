<?php
/**
 * AmiMultifeeds5/FAQ configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Config_AmiMultifeeds5_FAQ
 * @version   $Id: AmiMultifeeds5_Faq_TableItemModifier.php 42173 2013-10-10 12:57:54Z Leontiev Anton $
 * @since     x.x.x
 * @amidev
 */

/**
 * AmiMultifeeds5/FAQ configuration table item model modifier.
 *
 * @package    Config_AmiMultifeeds5_FAQ
 * @subpackage Model
 * @resource   {$modId}/table/model/item/modifier <code>AMI::getResourceModel('{$modId}/table')->getItem()->getModifier()*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_Faq_TableItemModifier extends Hyper_AmiMultifeeds5_TableItemModifier{
    /**
     * Model meta data processor.
     *
     * @var    string
     * @see    AMI_ModTableItemModifier::save()
     */
    protected $metaResId = 'faq/table/item/model/meta';
}

/**
 * AmiMultifeeds5/FAQ configuration model meta data processor.
 *
 * @package    Config_AmiMultifeeds5_FAQ
 * @subpackage Model
 * @see        AMI_ModTableItem::save()
 * @resource   {$modId}/table/item/model/meta <code>AMI::getResource('{$modId}/table/item/model/meta')*</code>
 * @since      x.x.x
 * @amidev
 */
class Faq_ModTableItemMeta extends AMI_ModTableItemMeta{
    /**
     * Field names to generate meta
     *
     * @var array
     */
    protected $aFieldSources = array(
        'header'   => 'question',
        'announce' => 'question',
        'body'     => 'answer'
    );
}
