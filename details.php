<?php

use airmoi\FileMaker\FileMakerException;

require_once('utilities.php');
require_once ('credentials_controller.php');
require_once ('constants.php');
require_once ('DatabaseSearch.php');
require_once ('Specimen.php');

session_set_cookie_params(0,'/','.ubc.ca',isset($_SERVER["HTTPS"]), true);
session_start();

define('DATABASE', $_GET['Database'] ?? null);
define('ACCESSIONNUMBER', $_GET['AccessionNo'] ?? null);

checkDatabaseField(DATABASE);

try {
    $databaseSearch = DatabaseSearch::fromDatabaseName(DATABASE);
} catch (FileMakerException $e) {
    $_SESSION['error'] = 'Unsupported database given';
    header('Location: error.php');
    exit;
}

try {
    $specimen = new Specimen(ACCESSIONNUMBER, $databaseSearch);
} catch (ErrorException | FileMakerException $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: error.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <link rel="stylesheet" href="https://herbweb.botany.ubc.ca/arcgis_js_api/library/4.10/esri/css/main.css">
        <link rel="stylesheet" href="public/css/details.css">
        <?php
            require_once ('partials/widgets.php');

            HeaderWidget('Specie Details');

            require_once('partials/conditionalCSS.php');
        ?>
    </head>

    <body>
        <?php Navbar(); ?>

        <?php TitleBannerSearch(DATABASE); ?>

        <div class="container-fluid flex-grow-1">
            <!-- basic info plus images -->
            <div class="row">
                <!-- information pane -->
                <div class="col-8 d-flex flex-column flex-md-row flex-md-wrap justify-content-center align-items-start align-items-md-end">
                    <?php
                    $count = 0;
                    foreach ($specimen->getFieldData() as $fieldName => $fieldValue): ?>
                        <div class="px-3 py-2 py-md-2 flex-fill responsive-columns-2">
                            <!-- field name and value -->
                            <div class="input-group">
                                <!-- field name with a to open collapsed info -->
                                <a data-bs-toggle="collapse" href="#collapsable<?php echo $count?>" role="button">
                                    <label class="input-group-text conditional-background-light"
                                           for="field-<?php echo htmlspecialchars(formatField($fieldName))?>">
                                        <?php echo htmlspecialchars(formatField($fieldName)) ?>
                                    </label>
                                </a>

                                <!-- field value --> <!-- TODO change back id of input and label to not use formatField() -->
                                <input class="form-control" type="text"
                                       id="field-<?php echo htmlspecialchars(formatField($fieldName))?>"
                                       name="<?php echo htmlspecialchars($fieldName)?>"
                                       readonly disabled value="<?= $fieldValue ?>" >
                            </div>
                            <!-- field information -->
                            <div class="collapse" id="collapsable<?=$count?>">
                                <div class="card card-body">
                                    This is some information for field <?=$fieldName?>!
                                </div>
                            </div>
                        </div>
                    <?php $count++; endforeach; ?>
                </div>

                <!-- image slideshow -->
                <div class="col">

                    <div id="imageCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-indicators">
                            <!-- loop over each image to add a button -->
                            <button type="button" data-bs-target="#imageCarousel" data-bs-slide-to="0" class="active"></button>
                        </div>
                        <div class="carousel-inner">
                            <!-- loop over each image to add it as a carousel-item -->
                            <div class="carousel-item active">
                                <img src="..." class="d-block w-100" alt="..">
                            </div>
                        </div>
                        <!-- back button -->
                        <button class="carousel-control-prev" type="button" data-bs-target="#imageCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <!-- forward button -->
                        <button class="carousel-control-next" type="button" data-bs-target="#imageCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- location map and information -->
            <div class="row">
                <!-- map -->
                <div class="col-3">
                    <?php if($specimen->getLatitude() !== null && $specimen->getLongitude() !== null) : ?>
                        <div id="viewDiv" style="height: 300px;"></div>
                        <script src="https://herbweb.botany.ubc.ca/arcgis_js_api/library/4.10/dojo/dojo.js"></script>
                        <script src="public/js/map.js"></script> <!-- TODO use specimen data insted of DOM data -->
                    <?php else: ?>
                        <div style="height: 300px; text-align:center; line-height:300px;">
                            <span style="">No coordinates for this record</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- information -->
                <div class="col">

                </div>
            </div>
        </div>

        <?php FooterWidget('public/images/beatyLogo.png') ;?>

    </body>
</html>