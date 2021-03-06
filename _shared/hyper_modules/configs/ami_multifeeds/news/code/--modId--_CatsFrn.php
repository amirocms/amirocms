<?php
/**
 * AmiMultifeeds/News configuration instance.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Module_##modId##
 * @version   $Id: --modId--_CatsFrn.php 45820 2013-12-23 12:02:21Z Leontiev Anton $
 * @since     6.0.2
 */

/**
 * AmiMultifeeds/News configuration front cats body type action controller.
 *
 * @package    Module_##modId##
 * @subpackage Controller
 * @resource   {$modId}/cats/controller/frn <code>AMI::getResource('{$modId}/module/controller/frn')*</code>
 * @since      6.0.2
 */
class ##modId##_CatsFrn extends AmiMultifeeds_News_CatsFrn{
}

/**
 * AmiMultifeeds/News configuration front cats body type view.
 *
 * @package    Module_##modId##
 * @subpackage View
 * @resource   {$modId}/cats/view/frn <code>AMI::getResource('{$modId}/cats/view/frn')*</code>
 * @since      6.0.2
 */
class ##modId##_CatsViewFrn extends AmiMultifeeds_News_CatsViewFrn{
}

/**
 * AmiMultifeeds/News configuration front subitems body type view.
 *
 * @package    Module_##modId##
 * @subpackage View
 * @resource   {$modId}/subitems/view/frn <code>AMI::getResource('{$modId}/subitems/view/frn')*</code>
 * @since      6.0.2
 */
class ##modId##_SubitemsViewFrn extends AmiMultifeeds_News_SubitemsViewFrn{
}

/**
 * AmiMultifeeds/News configuration front sticky cats body type action controller.
 *
 * @package    Module_##modId##
 * @subpackage Controller
 * @resource   {$modId}/items/controller/frn <code>AMI::getResource('{$modId}/items/controller/frn')*</code>
 * @since      6.0.2
 */
class ##modId##_StickyCatsFrn extends AmiMultifeeds_News_StickyCatsFrn{
}

/**
 * AmiMultifeeds/News configuration front sticky cats body type view.
 *
 * @package    Module_##modId##
 * @subpackage View
 * @resource   {$modId}/items/controller/frn <code>AMI::getResource('{$modId}/items/controller/frn')*</code>
 * @since      6.0.2
 */
class ##modId##_StickyCatsViewFrn extends Hyper_AmiMultifeeds_StickyCatsViewFrn{
}
