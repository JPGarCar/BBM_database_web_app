<!DOCTYPE html>
<html>
<head>
  <?php
    //session_start();
    //set_time_limit(0);
    require_once ('FileMaker.php');
    require_once ('partials/header.php');
    require_once ('functions.php');

    $numRes = 100;
    $layouts = $fm->listLayouts();
    $layout = "";
    foreach ($layouts as $l) {
      if ($_GET['Database'] === 'mi') {
        if (strpos($l, 'results') !== false) {
          $layout = $l;
          break;
        }
      }
      else if (strpos($l, 'results') !== false) {
        $layout = $l;
      }
    }

    function shouldDescend($field) {
      if (!isset($_GET['SortOrder']) || $_GET['SortOrder'] === '') return true;
      if (isset($_GET['Sort']) && $_GET['Sort'] === $field && isset($_GET['SortOrder']) && $_GET['SortOrder'] === 'Ascend') return true;
      return false;
    }

    $fmLayout = $fm->getLayout($layout);
    $layoutFields = $fmLayout->listFields();

    if (FileMaker::isError($layouts)) {
        echo $layouts->message;
        exit;
    }

    // Find on all inputs with values
    $findCommand = $fm->newFindCommand($layout);

    foreach ($layoutFields as $rf) {
      // echo $rf;
        $field = explode(' ',trim($rf))[0];
        if (isset($_GET[$field]) && $_GET[$field] !== '') {
            $findCommand->addFindCriterion($rf, $_GET[$field]);
        }
    }
     if (isset($_GET['Sort']) && $_GET['Sort'] != '') {
        $sortField = str_replace('+', ' ', $_GET['Sort']);
        $fieldSplit = explode(' ', $sortField);
        $sortBy = $_GET['Sort'];
        if (mapField($sortBy) === 'Accession Number') { 
          if ($_GET['Database'] == 'vwsp' or $_GET['Database'] == 'bryophytes' or 
              $_GET['Database'] == 'fungi' or $_GET['Database'] == 'lichen' or $_GET['Database'] == 'algae') {
            $sortBy = 'Accession Numerical';
          }
          else {
            $sortBy = 'sortNum';
          }
        } 
        if ($_GET['SortOrder'] === 'Descend') {
          // echo 'Descending';
          $findCommand->addSortRule(str_replace('+', ' ', $sortBy), 1, FILEMAKER_SORT_DESCEND);
        } else {
          // echo 'Ascending';
          $findCommand->addSortRule(str_replace('+', ' ', $sortBy), 1, FILEMAKER_SORT_ASCEND);
        }
    }

    if (isset($_GET['Page']) && $_GET['Page'] != '') {
        $findCommand->setRange(($_GET['Page'] - 1) * $numRes, $numRes);
    } else {
        $findCommand->setRange(0, $numRes);
    }

    $result = $findCommand->execute();

    if(FileMaker::isError($result)) {
        $findAllRec = [];
    } else {
        $findAllRec = $result->getRecords();
    }

    // echo __LINE__;
    // Check if layout exists, and get fields of layout
    If(FileMaker::isError($result)){
      // echo $result->message;
      echo 'No Records Found';
      // echo __LINE__;
      exit;
    } else {
      // echo __LINE__;
      $recFields = $result->getFields();
  ?>
  <style>
      th {
        font-size: 14px;
      }
      span {
        text-indent:-0.6em;
      }
      }
  </style>
</head>
<body>
<div class="container-fluid">
  <?php require_once ('partials/navbar.php'); ?>
  <?php require_once ('partials/pageController.php'); ?>
  <!-- construct table for given layout and fields -->
  <table class="table table-hover table-striped table-condensed tasks-table">
    <thead>
      <tr>
        <?php foreach($recFields as $i){
          if ($i === 'SortNum' || $i === 'Accession Numerical') continue;?>
        <th id = <?php echo formatField($i) ?> scope="col">
          <a style="padding: 0px;" href=
          <?php 
          
          // function shouldDescend($field) {
          //   if (!isset($_GET['SortOrder']) || $_GET['SortOrder'] === '') return true;
          //   if (isset($_GET['Sort']) && $_GET['Sort'] === $field) return true;
          // }
          // if (!isset($_GET['SortOrder']) || $_GET['SortOrder'] === '' || $_GET['SortOrder'] === 'Descend' || $i !== $_GET['Sort']) {
            if(isset($_GET['Page'])){
              $page = $_GET['Page'];
            }
            else {
              $page = '1';
            }
            if (shouldDescend($i)) {
              echo replaceURIElement(
                replaceURIElement(
                  replaceURIElement(
                    $_SERVER['REQUEST_URI'], 'Sort', replaceSpace($i))
                    , 'SortOrder', 'Descend')
                    , 'Page', $page);
            } else {
              echo replaceURIElement(
                replaceURIElement(
                  replaceURIElement(
                    $_SERVER['REQUEST_URI'], 'Sort', replaceSpace($i))
                    , 'SortOrder', 'Ascend')
                    , 'Page', $page);
            }
          ?>>
          <span id = "icon" class="fas fa-sort"><?php echo formatField($i) ?> </span>
          </a>
        </th>
        <?php }?>
      </tr>
    </thead>
    <tbody>
      <?php foreach($findAllRec as $i){?>
      <tr>
        <?php foreach($recFields as $j){
          if ($j === 'SortNum' || $j === 'Accession Numerical') continue;
          if(formatField($j) == 'Accession Number'){
            echo '<td id="data"><a style="padding: 0px;" href=\'details.php?Database=' . $_GET['Database'] . '&AccessionNo='.$i->getField($j).'\'>'.trim($i->getField($j)).'</a></td>';
          }
          else {
            echo '<td id="data">'.$i->getField($j).'</td>';
          }
        }?>
      </tr>
      <?php }?>
    </tbody>
  </table>  
  <?php }
  require ('partials/pageController.php');
  ?>
</div>
<?php require_once("partials/footer.php");?>
</body>
<?php 
 /*   $layouts = $fm->listLayouts();
    $layout = "";
    foreach ($layouts as $l) {
      if ($_GET['Database'] === 'mi') {
        if (strpos($l, 'results') !== false) {
          $layout = $l;
          break;
        }
      }
      else if (strpos($l, 'results') !== false) {
        $layout = $l;
      }
    }

    $fmLayout = $fm->getLayout($layout);
    $layoutFields = $fmLayout->listFields();

    if (FileMaker::isError($layouts)) {
        echo $layouts->message;
        exit;
    }

    // Find on all inputs with values
    $findCommand = $fm->newFindCommand($layout);
    // echo $layout;
    foreach ($layoutFields as $rf) {
      // echo $rf;
        $field = explode(' ',trim($rf))[0];
        if (isset($_GET[$field]) && $_GET[$field] !== '') {
            // echo $_GET[$field];
            // echo $rf;
            $findCommand->addFindCriterion($rf, $_GET[$field]);
        }
    }

    $findCommand->setRange(0, $found - 2000);

    $AllResults = $findCommand->execute();

    if (FileMaker::isError($AllResults)) {
      $error = 'FileMaker Find Error  (' . $AllResults->getMessage() . ')';
      echo $error;                          
    }

    $recFields = $AllResults->getFields();

    $allRecords = $AllResults->getRecords();

    $recordMatrix = [];
    foreach ($allRecords as $record) {
      // echo "hello";
      $recordInfo = [];
      foreach ($recFields as $rf) {
        $recordInfo[] = $record->getField($rf);
        // echo $recordInfo[0];
      }
      $recordMatrix[] = $recordInfo;
    }

    $_SESSION['recordMatrix'] = $recordMatrix;
    echo '<br>' . $_SESSION['recordMatrix'][1][0];*/
  ?>
</html>

