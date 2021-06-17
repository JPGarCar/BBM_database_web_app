<?php

use airmoi\FileMaker\FileMakerException;

require_once ('utilities.php');
require_once ('constants.php');
require_once ('DatabaseSearch.php');

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

$allFieldNames = array_keys($databaseSearch->getSearchLayout()->getFields());

# filter the layouts to those we only want
$ignoreValues = ['SortNum', 'Accession Numerical', 'Imaged', 'IIFRNo', 'Photographs::photoFileName', 'Event::eventDate', 'card01', 'Has Image', 'imaged'];

define("FIELDS", array_diff($allFieldNames, $ignoreValues));

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <link rel="stylesheet" href="https://herbweb.botany.ubc.ca/arcgis_js_api/library/4.10/esri/css/main.css">
        <?php
          require_once('partials/widgets.php');

          HeaderWidget('Search');
        require_once('partials/conditionalCSS.php');
        ?>
        <link rel="stylesheet" href="public/css/search.css">

        <!-- scripts -->
        <script type="text/javascript" src="public/js/process.js"></script>
    </head>

    <body>
        <?php Navbar(); ?>

        <!-- Page title below navbar -->
        <?php TitleBanner(databaseName: DATABASE, paddingIndex: 3); ?>

        <div class="container-fluid flex-grow-1">
            <form action="render.php" method="get" id="submit-form">
                <!-- hidden text field containing the database name -->
                <label>
                    <input type="text" hidden name="Database"
                           value=<?php echo htmlspecialchars(DATABASE); ?>>
                </label>

                <!-- search or show all -->
                <div class="d-flex justify-content-evenly p-1 align-items-center">
                    <!-- search or advanced search -->
                    <div class="flex-grow-1 px-5" style="max-width: 75%">
                        <div class="input-group">
                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#advancedSearchDiv">Advanced Search</button>
                            <input type="text" class="form-control form-control-lg" placeholder="Start a taxon search">
                            <button type="submit" onclick="submitForm()" class="btn btn-outline-primary conditional-background"> Search </button>
                        </div>
                        <div class="form-text">You can search for phylum, class, order, family, etc... </div>
                    </div>

                    <!-- show all, add mb-4 to align button to search bar -->
                    <div class="mb-4">
                        <button id="form" type="button" value="submit" onclick="submitForm()" class="btn btn-primary btn-lg conditional-background">Show All Records</button>
                    </div>
                </div>

                <div class="d-flex justify-content-around align-items-center px-5 py-3">
                    <div class="collapse" id="advancedSearchDiv">
                        <div class ="row">
                            <!-- form elements -->
                            <div>
                                <?php
                                list($layoutFields1, $layoutFields2) = array_chunk(FIELDS, ceil(count(FIELDS) / 2));
                                $count = 0;
                                foreach ($layoutFields1 as $layoutField) : ?>
                                    <div class="row">
                                        <!--- Section that is one label and one search box --->
                                        <div class="col-sm-3">
                                            <label for="field-<?php echo $layoutField?>">
                                                <?php echo htmlspecialchars(formatField($layoutField)) ?>
                                            </label>
                                        </div>

                                        <div class="col-sm-3">
                                            <input type="text" id="field-<?php echo $layoutField?>"
                                                <?php
                                                if (isset($_POST[str_replace(' ', '_', $layoutField)]))
                                                    echo "value=".htmlspecialchars($_POST[str_replace(' ', '_', $layoutField)]);
                                                ?>
                                                   name="<?php echo htmlspecialchars($layoutField) ?>"
                                                   class="form-control"
                                            >
                                        </div>

                                        <!--- End of a single label, input instance --->
                                        <?php if($count < sizeof($layoutFields2)) : ?>

                                            <!--- Section that is one label and one search box --->
                                            <div class="col-sm-3">
                                                <label for="field-<?php echo $layoutFields2[$count]?>">
                                                    <?php echo htmlspecialchars(formatField($layoutFields2[$count])) ?>
                                                </label>
                                            </div>

                                            <div class="col-sm-3">
                                                <input type="text" id="field-<?php echo $layoutFields2[$count]?>"
                                                    <?php
                                                    if (isset($_POST[str_replace(' ', '_', $layoutFields2[$count])]))
                                                        echo "value=".htmlspecialchars($_POST[str_replace(' ', '_', $layoutFields2[$count])]);
                                                    ?>
                                                       name="<?php echo htmlspecialchars($layoutFields2[$count]) ?>"
                                                       class="form-control"
                                                >
                                            </div>

                                            <!--- End of a single label, input instance --->
                                        <?php endif; ?>

                                    </div>
                                    <?php $count++; endforeach; ?>
                            </div>

                            <!-- search ops and submit button -->
                            <div class="d-inline-flex justify-content-evenly align-items-center py-4">

                                <!-- radio inputs have same name, so that only one can be enabled, and is used in render.php -->
                                <div class="btn-group">
                                    <span class="input-group-text"> Search with: </span>
                                    <input type="radio" class="btn-check" name="operator" id="and" value="and" checked autocomplete="off">
                                    <label class="btn btn-outline-secondary" for="and"> AND </label>

                                    <input type="radio" class="btn-check" name="operator" id="or" value="or" autocomplete="off">
                                    <label class="btn btn-outline-secondary" for="or"> OR </label>
                                </div>

                                <!-- only with image select, tooltip to explain why disabled -->
                                <div class="form-check form-switch" <?php if (!in_array(DATABASE, kDATABASES_WITH_IMAGES)) echo 'data-bs-toggle="tooltip" title="No images available"' ?>>
                                    <input type="checkbox" class="form-check-input" name="hasImage" <?php if (!in_array(DATABASE, kDATABASES_WITH_IMAGES)) echo 'disabled' ?>>
                                    <label for="imageCheck" class="form-check-label">
                                        Only show records that contain an image
                                    </label>
                                </div>

                                <!-- submit button -->
                                <div class="form-group">
                                    <button type="submit" onclick="submitForm()" class="btn btn-outline-primary conditional-background"> Search </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- footer -->
        <?php FooterWidget(imgSrc: 'public/images/beatyLogo.png'); ?>

    <script>
        let tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        let tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>
    </body>
</html>
