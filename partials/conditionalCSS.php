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
    "vwsp", "algae", "fungi", "bryophytes", "lichen" => "#6bd658",
    "miw", "mi" => "#ffc77a",
    "fish" => "#449adb",
    "entomology" => "#bb80ed",
    "fossil" => "#f76c68",
    default => "#ff545b",
};

echo "
    <style>
        /* Styles for background and border color */
        .conditional-background {
            background-color: $color;
            color: #FFFFFF;
            border-color: $color;
        }
        button.conditional-background:hover, button.conditional-background:active {
            background-color: $lightColor;
            border-color: $lightColor;
            box-shadow: 0 0 0 0.2rem $color;
        }
        
        .conditional-background-light {
            background-color: $lightColor;
            border-color: $lightColor;
            color: white;
        }
        .conditional-background-light:hover {
            background-color: $color;
            border-color: $color;
        }
        
        /* Styles for text color */
        .conditional-color {
            color: $color;
        }
        
        /* Change checkbox color to light when unchecked and color when checked
            https://stackoverflow.com/questions/44263892/how-to-style-a-clicked-button-in-css/44264124
         */
        .checkbox-conditional-background:checked,
        .checkbox-conditional-background:active,
        input[type='radio']:checked.radio-conditional-background + label,
        input[type='radio']:active.radio-conditional-background + label {
            background-color: $color;
            border-color: $color;
        }
        .checkbox-conditional-background:focus, 
        input[type='radio']:focus.radio-conditional-background + label {
            box-shadow: 0 0 0 0.2rem $color;
        }
        .checkbox-conditional-background, input[type='radio'].radio-conditional-background + label {
            background-color: $lightColor;
            border-color: $lightColor;
            color: #FFFFFF;
        }
        
        /* Styles for a outline only button */
        .conditional-outline-background {
            color: $color;
            outline-color: $color;
        }
        .conditional-outline-background:hover, .conditional-outline-background:focus {
            color: #FFFFFF;
            background-color: $color;
        }

        label.btn-custom, a.btn-custom,
        input.btn-custom, button.btn-custom{
            background-color: $color;
            color: #ffffff;
            border-color: $color;
        }
        
        /* Colors the highlight border of text inputs */
        .form-control:focus, .form-control-lg:focus {
            border-color: $color;
            box-shadow: 0 0 0 0.2rem $color;
        }

    </style>
";