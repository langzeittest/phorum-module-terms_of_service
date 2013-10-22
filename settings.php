<?php

// Make sure that this script is loaded from the admin interface.
if (!defined('PHORUM_ADMIN')) return;

// Install once custom profiles field
if (    !isset($PHORUM['mod_tos_installed'])
     || !$PHORUM['mod_tos_installed'] ) {
    include('./mods/terms_of_service/install.php');
}

// Save settings in case this script is run after posting
// the settings form.
if (    count($_POST)
     && isset($_POST['date_last_change'])
     && isset($_POST['file_name']) ) {

    // Check Date of last change
    $year = intval(substr($_POST['date_last_change'], 0, 4));
    $month = intval(substr($_POST['date_last_change'], 4, 2));
    $day = intval(substr($_POST['date_last_change'], 6, 2));
    if ( !(    $year
            && $month
            && $day
            && strlen($_POST['date_last_change']) == 8
            && checkdate($month, $day, $year) ) ) {
        $error = 'Invalid date.';
    } else {
        // Create the settings array for this module.
        $PHORUM['mod_tos'] = array
            ( 'date_last_change' => $_POST['date_last_change'],
              'default_accept' => $_POST['default_accept'],
              'file_name' => $_POST['file_name'] );

        // Force the default_accept to be an integer value.
        settype($PHORUM['mod_tos']['default_accept'], 'int');

        if (!phorum_db_update_settings(array('mod_tos'=>$PHORUM['mod_tos']))) {
            $error = 'Database error while updating settings.';
        } else {
            phorum_admin_okmsg('Settings Updated');
        }
    }
}

// Apply default values for the settings.
if (!isset($PHORUM['mod_tos']['date_last_change'])) {
    $PHORUM['mod_tos']['date_last_change'] = date('Ymd');
}

if (!isset($PHORUM['mod_tos']['default_accept'])) {
    $PHORUM['mod_tos']['default_accept'] = 0;
}

if (!isset($PHORUM['mod_tos']['file_name'])) {
    $PHORUM['mod_tos']['file_name'] = 'terms-of-service';
}

// We build the settings form by using the PhorumInputForm object.
include_once './include/admin/PhorumInputForm.php';
$frm =& new PhorumInputForm('', 'post', 'Save');
$frm->hidden('module', 'modsettings');
$frm->hidden('mod', 'terms_of_service');

// Here we display an error in case one was set by saving
// the settings before.
if (!empty($error)){
    phorum_admin_error($error);
}

$frm->addbreak('Edit settings for the Terms of Service module');
// Date of last change to the TOS
$row = $frm->addrow
    ( 'Date of last change to the Terms of Service (YYYYMMDD): ',
      $frm->text_box
          ( 'date_last_change',
            $PHORUM['mod_tos']['date_last_change'],
            8 ) );
$frm->addhelp
    ( $row,
      'Date of last change',
      "Set the date of the last change to the Terms of Service. This date is used to force users to re-accept the Terms of Services after modifications. It's recommended to use a date in the future." );
// Default settings for the accept-checkbox
$row = $frm->addrow
    ( 'Default value for the accept-checkbox',
       $frm->select_tag
           ( 'default_accept',
             array('unchecked', 'checked'),
             $PHORUM['mod_tos']['default_accept'] ) );
$frm->addhelp
    ( $row,
      'Default value for the accept-checkbox',
      'Define the default value for the accept-checkbox on the posting-form. If you use "unchecked" the user have to mark the checkbox for each post.' );
// File name
$row = $frm->addrow
    ( 'File name of the Terms of Service page: ',
      $frm->text_box
          ( 'file_name',
            $PHORUM['mod_tos']['file_name'],
            30 ) );
$frm->addhelp
    ( $row,
      'File name',
      "Change the file name if you want to use a localized name. Don't add the file extension \".php\". Take care that you have to change also the file name in the file system!" );
// Show settings form
$frm->show();

?>
