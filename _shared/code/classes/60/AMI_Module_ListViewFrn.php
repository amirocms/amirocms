<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Module_AMI_Module
 * @version   $Id: AMI_Module_ListViewFrn.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AMI_Module module front list component view.
 *
 * @package    Module_AMI_Module
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class AMI_Module_ListViewFrn extends AMI_ModListView_JSON{

    /**
     * Order column
     *
     * @var string
     */
    protected $orderColumn = 'date_created';

    /**
     * Order column direction
     *
     * @var bool
     */
    protected $orderDirection = 'desc';

    /**
     * Locale file name
     *
     * @var string
     */
    protected $localeFileName = 'templates/lang/modules/_list.lng';

    /**
     * Constructor.
     */
    public function __construct(){
        parent::__construct();

        // Init columns
        $this->addColumnType('id', 'hidden');
    }
}
