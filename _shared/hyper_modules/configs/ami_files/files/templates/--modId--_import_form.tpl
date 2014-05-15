##--system info: module_owner="modules" module="##modId##_import" system="1"--##
%%include_template "templates/hyper/data_exchange_form.tpl"%%

<!--#set var="add_btn" value="<input type="button" value="%%import_btn%%" class="but" onclick="CheckForm(); return false;""/>
"-->

<!--#set var="select_field(name=cat_id)" value="
<tr>
    <td nowrap>##element_caption##:&nbsp;</td>
    <td>##if(select)##<select id="cat_id" name="##name##"##attributes##>##select##</select>##else##%%no_categories%%##endif##</td>
</tr>
<tr><td colspan="2">&nbsp;</td></tr>
"-->

<!--#set var="checkbox_field" value="
<tr>
    <td colspan="2">
        <label>
            <input type="checkbox" id="cb_##name##" name="##name##" value="1"##checked## class="##filter_class##" helpId="form_##name##"##attributes## />
            ##element_caption##
        </label>
    </td>
</tr>
"-->

<!--#set var="section_form" value="
<script type="text/javascript">
AMI.Message.removeListener('ON_AMI_LIST_SHOW_ADD_BUTTON');
##scripts##

var
    _cms_document_form = '##modId##_import_form',
    _cms_document_form_changed = false,
    idCategory = 0;

function CheckForm(form){
    var numChecked = document.getElementsByName('_grp_num_checked')[0].value;

    // check category selected
    if(typeof(document.getElementById('cat_id')) == 'object' && document.getElementById('cat_id') != null){
        idCategory = document.getElementById('cat_id').value;
    }else{
        alert('%%no_categories_import%%');
        return false;
    }

    // check items selected
    if(isNaN(numChecked) || (parseInt(numChecked) <= 0)){
        alert('%%group_nonchecked_warn%%');
        return false;
    }

    _cms_document_form_changed = false;
    AMI.Page.doModuleAction('##modId##_import', AMI.Page.aModules['##modId##_import'].getComponentsByType('list')[0], 'group_action', {action:'grp_import', ami_full:1});

    return false;
}

AMI.Message.addListener('ON_COMPONENT_GET_REQUEST_DATA', function(oComponent, oParameters){
    if(oComponent.componentType == 'list' && oComponent.modAction == 'list_grp_import'){
        oParameters.cat_id = idCategory;
        oParameters.force_rewrite = document.getElementById('cb_force_rewrite').checked ? 1 : 0;
        oParameters.public = document.getElementById('cb_public').checked ? 1 : 0;
        oParameters.remove = document.getElementById('cb_remove').checked ? 1 : 0;
    }
    return true;
});
</script>

<div id="div_properties_form" class="main-form">
	<table ccc="1" border="0" cellpadding="0" cellspacing="0" ##if(width != '')##width="##width##"##endif## ##if(height != '')##height="##height##"##endif## style="margin-left:auto;margin-right:auto;">
		<tr>
			<td align=left id="add_left_top_img"></td>
			<td nowrap id="add_center_top_img">
				<span id="form_title" class="form-header">##header##</span>
			</td>
			<td nowrap id="add_right_info_top_img">
				<div id=stModified style="display:none;" class=form-header> [ %%modified%% ]</div>
			</td>
			<td id="add_right_top_img"></td>
		</tr>
		<tr>
			<td id="add_left_center_img"></td>
			<td colspan=2 class="table_sticker" valign="top">
<br>
<form action="" method=post enctype="multipart/form-data" name="##modId##_import_form" id="##modId##_import_form" onsubmit="return false;">
<input type="hidden" name="action" value="run" />
<input type="hidden" name="ami_full" value="" />
<table cellspacing="0" cellpadding="0" border="0" class="frm" width=100%>
<col class="first_column">
<col class="second_column">
##section_html##
</table>

<table cellspacing="0" cellpadding="0" border="0" class="frm" width=100%>
<col width="150">
<col width="*">
<tr>
<td colspan="2" align="right">
<br>
##form_buttons##
<br><br>
</td>
</tr>
<tr>
<td colspan="2">
 <sub>%%required_fields%%</sub>
</td>
</tr>
</table>
</form>
			</td>
			<td id="add_right_center_img"></td>
		</tr>
		<tr>
			<td id="add_left_bot_img"></td>
			<td id="add_center_bot_img" colspan=2></td>
			<td id="add_right_bot_img"></td>
		</tr>
	</table>
</div>
"-->