<?php

/**
 * Adds the color style to the page depending on the database in the Database query string.
 */

$color = match ($_GET['Database']) {
    "avian", "herpetology", "mammal" => "#70382D",
    "vwsp", "algae", "fungi", "bryophytes", "lichen" => "#3c8a2e",
    "miw", "mi" => "#ffb652",
    "fish" => "#165788",
    "entomology" => "#824bb0",
    "fossil" => "#bd3632",
    default => "#CC2229",
};

$lightColor = match ($_GET['Database']) {
    "avian", "herpetology", "mammal" => "#b57164",
    "vwsp", "algae", "fungi", "bryophytes", "lichen" => "#66e34f",
    "miw", "mi" => "#ffc77a",
    "fish" => "#449adb",
    "entomology" => "#bb80ed",
    "fossil" => "#f76c68",
    default => "#ff545b",
};

echo "
    <style>
        .conditional-background {
            background-color: $color;
            color: #FFFFFF;
            border-color: $color;
        }
        
        button.conditional-background:hover {
            background-color: $lightColor;
            border-color: $lightColor;
        }
        
        .conditional-color {
            color: $color;
        }

        input[type='radio'], input[type='button'] {
            background: $color;
            border-color: $color;
        }

        label.btn-custom, a.btn-custom,
        input.btn-custom, button.btn-custom{
            background-color: $color;
            color: #ffffff;
            border-color: $color;
        }

        a.btn-custom:hover,
        label.btn-custom:hover,
        input.btn-custom:hover,
        button.btn-custom:hover,
        .btn-custom.active,
        .btn-custom.active:hover {
            background-color: #49241c;
            color: #ffffff;
        }

        #jumbotron a, #table a{
            color: $color;
            text-decoration: none;
        }

        #jumbotron a:hover, #table a:hover {
            color: #49241c;
            text-decoration: none;
            background-color: inherit;
        }

        .previous {
            background-color: #f1f1f1;
            color: black;
            text-decoration: none;
        }

        .previous:hover {
            text-decoration: none;
        }

        .next {
            background-color: $color;
            color: white;
            text-decoration: none;
        }

        .next:hover {
            background-color: #49241c;
            color: white;
            text-decoration: none;
        }

        .round {
            border-radius: 50%;
        }

        th{
            color: $color;
        }

        a figcaption{
            color: $color;
            text-decoration: none;
        }

        .imageDiv a:hover {
            text-decoration: none;
        }

        .panel .panel-heading a h4{
            background-color:$color;
            color: #FFFFFF;
            text-decoration: none;
            padding:6px;
        }

        .panel .panel-heading a:hover, .panel .panel-heading a h4:hover {
            background-color:#49241c;
            text-decoration: none;
        }

    </style>
";