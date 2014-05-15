<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Module_Catalog
 * @version   $Id: EshopItem_FilterModelAdm_Serve_EshopOrder.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * EshopItem module item list component filter model.
 *
 * @package    Module_Catalog
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
class EshopItem_FilterModelAdm_Serve_EshopOrder extends AmiCatalog_Items_FilterModelAdm{
    /**
     * Constructor.
     */
    public function __construct(){
        parent::__construct();

        $this->dropViewFields(array('id_source', 'sticky'));
    }
}
