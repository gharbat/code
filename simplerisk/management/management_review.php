<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
* License, v. 2.0. If a copy of the MPL was not distributed with this
* file, You can obtain one at http://mozilla.org/MPL/2.0/. */

// Include required functions file
require_once(realpath(__DIR__ . '/../includes/functions.php'));
require_once(realpath(__DIR__ . '/../includes/authenticate.php'));
require_once(realpath(__DIR__ . '/../includes/display.php'));
require_once(realpath(__DIR__ . '/../includes/alerts.php'));
require_once(realpath(__DIR__ . '/../includes/permissions.php'));

// Include Zend Escaper for HTML Output Encoding
require_once(realpath(__DIR__ . '/../includes/Component_ZendEscaper/Escaper.php'));
$escaper = new Zend\Escaper\Escaper('utf-8');

// Add various security headers
add_security_headers();

// Add the session
$permissions = array(
        "check_access" => true,
        "check_riskmanagement" => true,
);
add_session_check($permissions);

// Include the CSRF Magic library
include_csrf_magic();

// Include the SimpleRisk language file
require_once(language_file());

// Record the page the workflow started from as a session variable
$_SESSION["workflow_start"] = $_SERVER['SCRIPT_NAME'];

// If reviewed is passed via GET
if (isset($_GET['reviewed']))
{
  // If it's true
  if ($_GET['reviewed'] == true)
  {
    // Display an alert
    set_alert(true, "good", "Management review submitted successfully!");
  }
}

// If mitigated was passed back to the page as a GET parameter
if (isset($_GET['mitigated']))
{
  // If its true
  if ($_GET['mitigated'] == true)
  {
    // Display an alert
    set_alert(true, "good", "Mitigation submitted successfully!");
  }
}
?>

<!doctype html>
<html>

<head>
    <script src="../js/jquery.min.js"></script>
    <script src="../js/jquery-ui.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/jquery.dataTables.js"></script>
    <script src="../js/cve_lookup.js"></script>
    <script src="../js/sorttable.js"></script>
    <script src="../js/common.js"></script>
    <script src="../js/pages/risk.js"></script>
    <script src="../js/highcharts/code/highcharts.js"></script>
    <script src="../js/moment.min.js"></script>
    <script src="../js/bootstrap-multiselect.js"></script>
    <script src="../js/jquery.blockUI.min.js"></script>

    <title>SimpleRisk: Enterprise Risk Management Simplified</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/bootstrap-responsive.css">
    <link rel="stylesheet" href="../css/jquery.dataTables.css">
    
    <link rel="stylesheet" href="../css/divshot-util.css">
    <link rel="stylesheet" href="../css/divshot-canvas.css">
    <link rel="stylesheet" href="../css/style.css">

    <link rel="stylesheet" href="../bower_components/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../css/theme.css">
    <link rel="stylesheet" href="../css/side-navigation.css">
    <link rel="stylesheet" href="../css/bootstrap-multiselect.css">

    <link rel="stylesheet" href="../css/selectize.bootstrap3.css">
    <script src="../js/selectize.min.js"></script>

    <?php
        setup_favicon("..");
        setup_alert_requirements("..");
    ?>    

</head>

<body>
    <?php
        view_top_menu("RiskManagement");

    ?>
    <?php  
    // Get any alert messages
    get_alert();
    ?>
    <style>
        .dataTables_filter, .dataTables_info { display: none; }
    </style>    
    <div class="tabs new-tabs">
        <div class="container-fluid">
            <div class="row-fluid">
                <div class="span3"> </div>
                <div class="span9">
                    <div class="tab-append">
                        <div class="tab selected form-tab tab-show new" >
                            <div>
                                <span>
                                    <!--<a href="management_review.php"><?php echo $escaper->escapeHtml($lang['RiskList']); ?></a>-->
                                    <?php echo $escaper->escapeHtml($lang['RiskList']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span3">
                <?php view_risk_management_menu("PerformManagementReviews"); ?>
            </div>
            <div class="span9">
                <div id="tab-content-container" class="row-fluid">
                    <div id="tab-container" class="tab-data">
                        <div class="row-fluid">
                            <div class="span10">
                                <p><?php echo $escaper->escapeHtml($lang['ManagementReviewHelp']); ?>.</p>
                            </div>
                            <div class="span2 text-right">
                                <a href="#setting_modal" class="btn" title="<?php echo $escaper->escapeHtml($lang['Settings']);?>" role="button" data-toggle="modal"><i class="fa fa-gear"></i></a>
                            </div>
                        </div>
                        <div class="row-fluid">
                            <div class="span12 ">
                                <?php display_management_review(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" id="_delete_tab_alert" value="<?php echo $escaper->escapeHtml($lang['Are you sure you want to close the risk? All changes will be lost!']); ?>">
    <input type="hidden" id="enable_popup" value="<?php echo $escaper->escapeHtml(get_setting('enable_popup')); ?>">
    <?php display_set_default_date_format_script(); ?>

    <!-- MODEL WINDOW FOR CONTROL DELETE CONFIRM -->
    <div id="setting_modal" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="setting_modal" aria-hidden="true" style="width:800px;">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><?php echo $escaper->escapeHtml($lang['Settings']); ?></h4>
        </div>
        <div class="modal-body">
            <form id="custom_display_settings" name="custom_display_settings" method="post">
            <?php echo display_custom_risk_columns("custom_perform_reviews_display_settings");?>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-default" data-dismiss="modal" aria-hidden="true"><?php echo $escaper->escapeHtml($lang['Cancel']); ?></button>
            <button type="submit" id="save_display_settings" class="btn btn-danger"><?php echo $escaper->escapeHtml($lang['Save']); ?></button>
        </div>
    </div>

</body>

</html>
