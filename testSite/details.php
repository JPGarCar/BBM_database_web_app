<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="https://herbweb.botany.ubc.ca/arcgis_js_api/library/4.10/esri/css/main.css">
<style>
  html, body, #map {
    height: 100%;
    width: 100%;
    margin: 0;
    padding: 0;
  }
  #zoology {
    height:10%;
  }
  #fish{
    height:20%;
  }
</style>
<?php
 session_start();
  require_once ('FileMaker.php');
  require_once ('partials/header.php');
  require_once ('functions.php');

  require ('lib/simple_html_dom.php');

  $layouts = $fm->listLayouts();
  $layout = $layouts[0];
  foreach ($layouts as $l) {
    if ($_GET['Database'] === 'mi') {
      if (strpos($l, 'details') !== false) {
        $layout = $l;
        break;
      }
    }
    else if (strpos($l, 'details') !== false) {
      $layout = $l;
    }
  }

  // echo $_GET['AccessionNo'];

  $findCommand = $fm->newFindCommand($layout);
  if (isset($_GET['AccessionNo']) && $_GET['AccessionNo'] !== '') {
      if ($_GET['Database'] == 'vwsp' or $_GET['Database'] == 'bryophytes' or 
      $_GET['Database'] == 'fungi' or $_GET['Database'] == 'lichen' or $_GET['Database'] == 'algae'){
        $findCommand->addFindCriterion('Accession Number', '=='.$_GET['AccessionNo']);
      }
      else if ($_GET['Database'] == 'fossil' || $_GET['Database'] == 'avian' || $_GET['Database'] == 'herpetology' || $_GET['Database'] == 'mammal') {

        $findCommand->addFindCriterion('catalogNumber', '=='.$_GET['AccessionNo']);
      }
      else if ($_GET['Database'] == 'fish'){
        
        $findCommand->addFindCriterion('Accession Number', '=='.$_GET['AccessionNo']);
    
      }
      else if ($_GET['Database'] == 'entomology'){
        $findCommand->addFindCriterion('SEM #', '=='.$_GET['AccessionNo']);
      }
      else {
        $findCommand->addFindCriterion('Accession No.', '=='.$_GET['AccessionNo']);
      }
  }

  $result = $findCommand->execute();

  if(FileMaker::isError($result)) {
    $_SESSION['error'] = $result->getMessage();
    header('Location: error.php');
    exit;
  } else {
      $findAllRec = $result->getRecords();
  }
  ?>
</head>

<body>
  <div class="container-fluid">
  <?php
  require_once ('partials/navbar.php');
  // Check if layout exists, and get fields of layout
  If(FileMaker::isError($result)){
    $_SESSION['error'] = $result;
    header('Location: error.php');
    exit;
  } else {
    $recFields = $result->getFields();
  ?>
  <!-- construct table for given layout and fields -->
  <table class="table">
    <tbody>
      <?php foreach($recFields as $i){
        if ($i === "Photographs::photoContainer" || $i === "Photographs::stableURL" || $i === "IIFRNo") continue;?> 
      <tr>
        <th scope="col"><?php echo htmlspecialchars(formatField($i)) ?></th>
        <td 
        <?php if (formatField($i) === "Latitude") {echo "id='Latitude'"; $lat = $findAllRec[0]->getField($i);}
              if (formatField($i) === "Longitude") {echo "id='Longitude'"; $long = $findAllRec[0]->getField($i);}?>>
              <?php echo htmlspecialchars($findAllRec[0]->getField($i)) ?>
        </td>
      </tr>
      <?php }?>
    </tbody>
  </table>   
  <?php } ?>
  <div class="row">
  <div class="col-sm-6">
    <?php   
    if($lat != "" && $long != ""){?> 
      <div id="viewDiv" style="height: 300px;"></div> 
    <script src="https://herbweb.botany.ubc.ca/arcgis_js_api/library/4.10/dojo/dojo.js"></script>
    <script src="js/map.js"></script>
    <?php }?>
  </div>
    <div class="col-sm-6">
      <?php
          if ($_GET['Database'] === 'vwsp') {
            $url = "https://herbweb.botany.ubc.ca/testSite/images/vwsp_images/Large_web/".$_GET['AccessionNo'].".jpg";
            if(@getimagesize($url)[0] >0 && @getimagesize($url)[1] > 0){
              echo '<a href="'.$url.'" target="_blank"><img src ="'. $url.'"></a>';
            }
          }
          else if ($_GET['Database'] === 'algae') {
            $url = "https://herbweb.botany.ubc.ca/testSite/images/ubcalgae_images/Large_web/".$_GET['AccessionNo'].".jpg";
            if(@getimagesize($url)[0] >0 && @getimagesize($url)[1] > 0){
              echo '<a href="'.$url.'" target="_blank"><img src ="'. $url.'"></a>';
            }
          }
          else if ($_GET['Database'] === 'lichen') {
            $url = "https://herbweb.botany.ubc.ca/testSite/images/lichen_images/Large_web/".$_GET['AccessionNo'].".jpg";
            if(@getimagesize($url)[0] >0 && @getimagesize($url)[1] > 0){
              echo '<a href="'.$url.'" target="_blank"><img src ="'. $url.'"></a>';
            }
          }
          else if ($_GET['Database'] === 'fungi') {
            $url = "https://herbweb.botany.ubc.ca/testSite/images/fungi_images/Large_web/".$_GET['AccessionNo'].".jpg";
            if(@getimagesize($url)[0] >0 && @getimagesize($url)[1] > 0){
              echo '<a href="'.$url.'" target="_blank"><img src ="'. $url.'"></a>';
            }
          }
          else if ($_GET['Database'] === 'bryophytes') {
            $url = "https://herbweb.botany.ubc.ca/testSite/images/bryophytes_images/Large_web/".$_GET['AccessionNo'].".jpg";
            if(@getimagesize($url)[0] >0 && @getimagesize($url)[1] > 0){
              echo '<a href="'.$url.'" target="_blank"><img src ="'. $url.'"></a>';
            }
          }
          if ($_GET['Database'] === 'mammal') {
            $url = 'https://collections.zoology.ubc.ca/fmi/xml/cnt/data.JPG?-db=Mammal%20Research%20Collection&-lay=mammal_details&-recid='.htmlspecialchars($findAllRec[0]->getRecordID()).'&-field=Photographs::photoContainer(1)';
            //url is not just https:// ie there is data in the container
            if (@getimagesize($url)[0] >0 && @getimagesize($url)[1] > 0) 
              echo '<a href ='. htmlspecialchars($url).'>'.'<img id = "zoology" src="'.htmlspecialchars($url) .'"></a>';
          }
          if ($_GET['Database'] === 'avian') {

            $url = 'https://collections.zoology.ubc.ca/fmi/xml/cnt/data.JPG?-db=Avian%20Research%20Collection&-lay=details-avian&-recid='.htmlspecialchars($findAllRec[0]->getRecordID()).'&-field=Photographs::photoContainer(1)';
            if (@getimagesize($url)[0] >0 && @getimagesize($url)[1] > 0) 
              echo '<a href ='. htmlspecialchars($url).'>'.'<img id = "zoology" src="'.htmlspecialchars($url) .'"></a>';
          }
          if ($_GET['Database'] === 'herpetology') {

            $url = 'https://collections.zoology.ubc.ca/fmi/xml/cnt/data.JPG?-db=Herpetology%20Research%20Collection&-lay=herp_details&-recid='.htmlspecialchars($findAllRec[0]->getRecordID()).'&-field=Photographs::photoContainer(1)';
            if (@getimagesize($url)[0] >0 && @getimagesize($url)[1] > 0) 
              echo '<a href ='. htmlspecialchars($url).'>'.'<img id = "zoology" src="'.htmlspecialchars($url) .'"></a>';
          }
          if ($_GET['Database'] === 'entomology') {
              //check if image url actually exists
              $genusPage = getGenusPage($findAllRec[0]);
              $genusSpecies = getGenusSpecies($findAllRec[0]);
              // echo $genusPage;
              $html = file_get_html($genusPage);
              $species = $html->find('.speciesentry');
              // echo $html;
              foreach($species as $spec) {
                $speciesName = $spec->innertext;
                if (strpos($speciesName, $genusSpecies) !== false ) {
                  echo $speciesName;
                  $images = $spec->find('a');
                  $link = $images[0]->href;
                  $url = $genusPage . $link;
                  echo '<img src ="'. $url.'">';
                  break;
                }
              }
          }
          if ($_GET['Database'] === 'fish') {
            $url = 'https://open.library.ubc.ca/media/download/jpg/fisheries/1.0200849/0';
            $linkToWebsite = 'https://open.library.ubc.ca/collections/fisheries/items/1.0200849';
            echo '<a href ='. htmlspecialchars($linkToWebsite).'>'.'<img id = "fish" src="'.htmlspecialchars($url) .'"></a>';
          }

          function getGenusPage($record) {
            $order = $record->getField('Order');
            $family = $record->getField('Family');
            $genusPage = 'http://www.zoology.ubc.ca/entomology/main/'.$order.'/'.$family.'/';
            return $genusPage;
          }

          function getGenusSpecies($record) {
            $genus = $record->getField('Genus');
            $species = $record->getField('Species');
            $genusSpecies = $genus . ' ' . $species ;
            return $genusSpecies;
          }
      ?>
    </div>
  </div>
  <?php require_once("partials/footer.php");?>
  </div>
</body>
</html>