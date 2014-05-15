<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Environment
 * @version   $Id: AMI_ErrorHandler_Message.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary?
 */

$isCommonError = !defined('AMI_DB_ERROR');

$text =
'<!doctype html public "-//w3c//dtd html 4.0 transitional//en">
<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><body></td></tr></table><br>
  <!-- This is an error message! Read the text below. -->
  <div style="text-align:center;"><div style="
    width:440px !important;
    margin:5px auto 0px auto !important;
    font-family: Tahoma !important;
    font-size: 12px !important;
    border-radius: 10px !important;
    border: 2px black solid !important;
      border-color: red !important;
    padding:10px !important;
      color: #000 !important;
      background-color: #eee !important;
        visibility:visible !important;
        opacity:1 !important;
      display:block !important;
    text-align:left;">
    <div style="color: #f00 !important; font-size: 16px !important;">' . ($isCommonError ? 'System error' : 'Database error') . '</div>
    <div style="color: #f00 !important; font-size: 10px !important;">Server time: ' . date('Y-m-d H:i:s') . '</div>';

if($isCommonError){
    $text .=
'    <p>This error is related to server technical problems.  This can happen due to a wrong system configuration, wrong access permissions, disc space problems, database failure, other problems. </p>
    <p>The detailed problem description is generally available in log file <span style="white-space: nowrap;">&laquo;_admin/_logs/err.log&raquo;</span></p>';
}else{
    $text .=
'    <p>This error is related to database failures. This can be caused by wrong database access permissions, exceeding database query limits, table(s) structure and/or consistency failure, wrong SQL query and other problems.</p>
    <p>The detailed problem description is generally available in log file <span style="white-space: nowrap;">&laquo;_admin/_logs/err.log&raquo;</span></p>';
}

if(ADMIN_LOGIN_LANG === 'ru'){
    $text .=
    '<div style="height:2px; background: lightgray;margin:10px 0px;font-size:0px;"></div>
    <div style="color: #f00 !important; font-size: 16px !important;">' . ($isCommonError ? 'Системная ошибка' : 'Ошибка базы данных') . '</div>
    <div style="color: #f00 !important; font-size: 10px !important;">Время сервера: ' . date('Y-m-d H:i:s') . '</div>';

    if($isCommonError){
        $text .=
'    <p>Данная ошибка связана с техническими проблемами на сервере. Это может быть неверная конфигурация системы, некорректные права доступа, проблемы с доступным местом на диске, сбоем работы базы данных и другими проблемами.</p>
    <p>Подробная информация об ошибке обычно доступна в журнале, находящемся в файле <span style="white-space: nowrap;">&laquo;_admin/_logs/err.log&raquo;</span></p>';
    }else{
        $text .=
'    <p>Данная ошибка связана с проблемами работы базы данных. Это может быть связано с неверными параметрами доступа к БД, превышением лимитов на количество запросов к БД, нарушением структуры и целостности таблиц, ошибочным SQL запросом и другими проблемами.</p>
    <p>Подробная информация об ошибке обычно доступна в журнале, находящемся в файле <span style="white-space: nowrap;">&laquo;_admin/_logs/err.log&raquo;</span></p>';
    }
}

$text .=
'  </div></div>
</body></html>';

return $text;
