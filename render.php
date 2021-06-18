<?php

use airmoi\FileMaker\FileMakerException;
use airmoi\FileMaker\Object\Field;

require_once('utilities.php');
require_once ('DatabaseSearch.php');
require_once ('credentials_controller.php');

session_set_cookie_params(0,'/','.ubc.ca',isset($_SERVER["HTTPS"]), true);
session_start();

define("DATABASE", $_GET['Database'] ?? null);

checkDatabaseField(DATABASE);

try {
    $databaseSearch = DatabaseSearch::fromDatabaseName(DATABASE);
} catch (FileMakerException $e) {
    $_SESSION['error'] = 'Unsupported database given';
    header('Location: error.php');
    exit;
}

# get the fields for the search and result layout
$searchLayoutFieldNames = $databaseSearch->getSearchLayout()->listFields();
$resultLayoutFields = $databaseSearch->getResultLayout()->listFields();

$searchLayoutFields = $databaseSearch->getSearchLayout()->getFields();

$maxResponses = 30;

# remove any empty get fields
$usefulGETFields = array_filter($_GET);

if ($_GET['taxon-search'] ?? null) {
    try {
        $result = $databaseSearch->queryTaxonSearch($_GET['taxon-search'], $maxResponses, $_GET['Page'] ?? 1);
    } catch (FileMakerException $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: error.php');
        exit;
    }
} else {
    # since we are diffing by keys, we need to set dummy values
    $unUsedGETFields = ['operator' => '', 'Sort' => '', 'Page' => '', 'SortOrder' => '', 'Database' => ''];
    $usefulGETFields = array_diff_key($usefulGETFields, $unUsedGETFields);

    try {
        $result = $databaseSearch->queryForResults($maxResponses, $usefulGETFields, $_GET['operator'] ?? 'and',
            $_GET['Sort'] ?? null, $_GET['Page'] ?? 1, $_GET['SortOrder'] ?? null);
    } catch (FileMakerException $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: error.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <?php
            require_once('partials/widgets.php');
            HeaderWidget('Search Table');
            require_once('partials/conditionalCSS.php');
        ?>
        <link rel="stylesheet" href="public/css/render.css">
        <link rel="stylesheet" href="public/css/advanced-search.css">
    </head>

    <body>

        <!-- navbar -->
        <?php Navbar(); ?>

        <!-- Page title below navbar -->
        <?php TitleBannerRender(database: DATABASE, recordNumber: $result->getFoundSetCount()); ?>

        <!-- main body with table and its widgets -->
        <div class="container-fluid flex-grow-1">

            <div class="py3">
                <!-- review search parameters -->
                <button type="button" data-bs-toggle="collapse" data-bs-target="#advancedSearchDiv"
                        class="btn btn-outline-secondary order-1 order-md-0 conditional-outline-background"
                        >Review Search Parameters</button>

                <!-- edit table columns -->

                <!-- enable/disable images -->

                <!-- download data -->

                <!-- start a new search -->
            </div>

            <!-- edit advanced search collapsible -->
            <div class="collapse w-100" id="advancedSearchDiv">
                <div class="d-flex justify-content-around align-items-center px-5 py-3">
                    <form action="render.php" method="get" id="submit-form">
                        <!-- hidden text field containing the database name -->
                        <label>
                            <input type="text" hidden id="Database" name="Database" value=<?php echo htmlspecialchars(DATABASE); ?>>
                        </label>
                        <!--
                            form elements,
                            using flex and media queries, we have one, two or three columns
                            refer to the view css to media queries, we followed bootstrap cutoffs
                         -->
                        <div class="d-flex flex-column flex-md-row flex-md-wrap justify-content-center align-items-start align-items-md-end">
                            <?php
                            # Loop over all fields and create a field element in the form for each!
                            $count = 0;
                            /** @var string $fieldName
                              * @var Field $field */
                            foreach ($searchLayoutFields as $fieldName => $field) : ?>

                                <div class="px-3 py-2 py-md-1 flex-fill responsive-columns">
                                    <!-- field name and input -->
                                    <div class="input-group">
                                        <a data-bs-toggle="collapse" href="#collapsable<?php echo $count?>" role="button">
                                            <label class="input-group-text conditional-background-light"
                                                   for="field-<?php echo htmlspecialchars($fieldName)?>">
                                                <?php echo htmlspecialchars(formatField($fieldName)) ?>
                                            </label>
                                        </a>
                                        <?php
                                        # Try to get a list of options, if error (aka none available) then no datalist
                                        try {
                                            $fieldValues = $field->getValueList();
                                        } catch (FileMakerException $e) { /* Do nothing */ }

                                        if (isset($fieldValues)) : ?>
                                            <input class="form-control" list="datalistOptions"
                                                   placeholder="Type to search" id="field-<?php echo htmlspecialchars($fieldName)?>"
                                                   name="<?php echo htmlspecialchars($fieldName)?>">
                                            <datalist id="datalistOptions">
                                                <?php foreach ($fieldValues as $fieldValue): ?>
                                                    <option value="<?=$fieldValue?>"></option>
                                                <?php endforeach; ?>
                                            </datalist>
                                        <?php else:
                                            $value = array_key_exists($fieldName, $_GET) ? $_GET[$fieldName] : null;
                                            ?>
                                            <input class="form-control" type="<?php echo $field->getResult() ?>"
                                                   id="field-<?php echo htmlspecialchars($fieldName)?>"
                                                   name="<?php echo htmlspecialchars($fieldName)?>"
                                                   value="<?=$value?>">
                                        <?php endif; ?>
                                    </div>
                                    <!-- field information -->
                                    <div class="collapse" id="collapsable<?php echo $count?>">
                                        <div class="card card-body">
                                            This is some information for field <?=$fieldName?>!
                                        </div>
                                    </div>
                                </div>
                                <?php $count++; endforeach; ?>
                        </div>

                        <!-- search ops and submit button -->
                        <div class="d-inline-flex justify-content-evenly align-items-center py-4 w-100">

                            <!-- radio inputs have same name, so that only one can be enabled, and is used in render.php -->
                            <div class="btn-group">
                                <span class="input-group-text"> Search with: </span>
                                <!-- we go with checked if operator does not exist since that's the default -->
                                <input type="radio" class="btn-check radio-conditional-background"
                                       name="operator" id="and" value="and"
                                       <?php echo array_key_exists('operator', $_GET) ? $_GET['operator'] == 'and' ? 'checked' : '' : 'checked' ?>
                                       autocomplete="off">
                                <label class="btn btn-outline-secondary" for="and"> AND </label>

                                <input type="radio" class="btn-check radio-conditional-background"
                                       name="operator" id="or" value="or"
                                       <?php echo array_key_exists('operator', $_GET) ? $_GET['operator'] == 'or' ? 'checked' : '' : '' ?>
                                       autocomplete="off">
                                <label class="btn btn-outline-secondary" for="or"> OR </label>
                            </div>

                            <!-- only with image select, tooltip to explain why disabled -->
                            <div class="form-check form-switch" <?php if (!in_array(DATABASE, kDATABASES_WITH_IMAGES)) echo 'data-bs-toggle="tooltip" title="No images available"' ?>>
                                <label class="form-check-label">
                                    <input type="checkbox" class="form-check-input checkbox-conditional-background"
                                           <?php if (!in_array(DATABASE, kDATABASES_WITH_IMAGES)) echo 'disabled' ?>
                                           <?php echo array_key_exists('hasImage', $_GET) ? $_GET['hasImage'] == 'on' ? 'checked' : '' : '' ?>
                                           name="hasImage">
                                    Only show records that contain an image
                                </label>
                            </div>

                            <!-- submit button -->
                            <div class="form-group">
                                <button type="submit" onclick="submitForm()" class="btn btn-outline-primary conditional-background"> Advanced Search </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modify Search Button -->
            <div class="form-group">
                <form method=post action=<?php echo "search.php"."?Database=".htmlspecialchars(DATABASE);?>>
                    <?php
                    # will add the search params as hidden inputs to use in future sort or page calls
                    foreach ($_GET as $key=>$value) {
                        if (in_array($key, $searchLayoutFieldNames) || (in_array(str_replace('_', ' ', $key), $searchLayoutFieldNames))) {
                            echo "<input  type=hidden value=".htmlspecialchars($value)." name=".htmlspecialchars($key).">";
                        }
                    }
                    ?>
                    <button type="submit" value = "Submit" class="btn btn-custom">Modify Search</button>
                </form>
            </div>

            <?php $databaseSearch->echoDataTable($result); ?>

            <div class="p-3">
                <?php TableControllerWidget($maxResponses, $result); ?>
            </div>
        </div>

        <!-- footer -->
        <?php FooterWidget(imgSrc: 'public/images/beatyLogo.png'); ?>

        <!-- scripts -->
        <script type="text/javascript" src="public/js/advanced-search.js"></script>
    </body>
</html>