<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Module_Pages
 * @version   $Id: Pages_Table.php 42102 2013-10-07 11:53:16Z Leontiev Anton $
 * @since     5.12.0
 */

/**
 * Pages module table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * Pages fields description:
 * - <b>id</b> - page identifier (int),
 * - <b>id_parent</b> - parent page id (int),
 * - <b>public</b> - flag specifying front-side item displaying (0/1),
 * - <b>lang</b> - item locale (string, 2-3 chars),
 * - <b>header</b> - item header (string),
 * - <b>id_owner</b> - owner user id (int).
 * - <b>date_modified</b> - page modification date,
 * - <b>id_mod</b> - page module name (string),
 * - <b>position</b> - page position ion the range of siblings (int),
 * - <b>all_parents</b> - comma separated parent page ids (string),
 * - <b>is_section</b> - flag specifying if page is main for the section (0/1),
 * - <b>is_printable</b> - flag specifying to allow print version of the page (0/1),
 * - <b>skip_search</b> - flag specifying to don't use the page in search (0/1),
 * - <b>show_in_sitemap</b> - flag specifying to show the page in sitemap (0/1),
 * - <b>show_at_parent</b> - flag specifying to show link to this page on the parent page (0/1),
 * - <b>show_siblings</b> - show links to sibling pages on the page (0/1),
 * - <b>menu_main</b> - flag specifying to show the page in the main menu (0/1),
 * - <b>menu_top</b> - flag specifying to show the page in the top menu (0/1),
 * - <b>menu_bottom</b> - flag specifying to show the page in the bottom menu (0/1),
 * - <b>html_title_inherit</b> - inherit HTML meta title from parent page (0/1),
 * - <b>html_title</b> - HTML meta title (string),
 * - <b>html_keywords</b> - Comma separated HTML meta keywords (string),
 * - <b>html_description</b> - HTML meta description (string),
 * - <b>html_head_code</b> - HTML head tag code (string),
 * - <b>noindex_link</b> - flag specifying to do not allow to browse this link in menu for search engine robots (noindex, nofollow) (0/1),
 * - <b>noindex_page</b> - flag specifying to do not allow to index page by search engine robots (0/1).
 *
 * @package    Module_Pages
 * @subpackage Model
 * @resource   pages/table/model <code>AMI::getResourceModel('pages/table')</code>
 * @since      5.12.0
 * @todo       Allow to create/update pages.
 */
class Pages_Table extends AMI_ModTable{
    // * - <b>id_redirect</b> - page id if page type is link (int),
    /**
     * Database table name
     *
     * @var string
     */
    protected $tableName = 'cms_pages';

    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model 
     * @todo  Describe several fields
     */
    public function __construct(array $aAttributes = array()){
        $this->disableHTMLFields();
        $this->addSystemFields(
            array(
                'redirect_id', 'parea_id', 'removable', 'hidden', 'fixed_name', 'visible_area',
                'lay_id', 'lay_f1_body', 'lay_f2_body', 'lay_f3_body', 'lay_f4_body', 'lay_f5_body',
                'lay_f6_body', 'lay_f7_body', 'lay_f8_body', 'lay_f9_body', 'lay_f10_body', 'body',
                'tmp_date', 'tmp_record', 'user_script', 'ip_area', 'block_mask', 'sb_data',
                'last_modified', 'is_active', 'redirection_code', 'dest_link', 'id_dataset', 'id_site',
                'img_menu_normal', 'img_menu_over', 'img_menu_active', 'tpl_addon', 'skip_in_prevnext',
                'is_kw_manual' // , 'html_head_tail'
            )
        );

        parent::__construct($aAttributes);

        $aRemap = array(
            'id'             => 'id',
            'public'         => 'public',
            'lang'           => 'lang',
            'id_parent'      => 'parent_id',
            'id_mod'         => 'module_name',
            'header'         => 'name',
            'sublink'        => 'script_link',
            'date_modified'  => 'last_changed',
            'show_at_parent' => 'show_me_at_parent',
            'html_head_code' => 'html_head_tail',
            'noindex_link'   => 'use_noindex',
            'noindex_page'   => 'page_noindex',
        );
        $this->addFieldsRemap($aRemap);
    }
}

/**
 * Pages module table item model.
 *
 * @package    Module_Pages
 * @subpackage Model
 * @resource   pages/table/model/item <code>AMI::getResourceModel('pages/table')->getItem()</code>
 * @since      5.12.0
 */
class Pages_TableItem extends AMI_ModTableItem{
    /**
     * Saves current item data.
     *
     * @return void
     * @amidev
     */
    public function save(){
        trigger_error('Forbidden!', E_USER_ERROR);
    }
}

/**
 * Pages module table list model.
 *
 * @package    Module_Pages
 * @subpackage Model
 * @resource   pages/table/model/list <code>AMI::getResourceModel('pages/table')->getList()</code>
 * @since      5.12.0
 */
class Pages_TableList extends AMI_ModTableList{
}
