<?php
/*

 Unless otherwise noted, code in this file is copyright (C) 2017 Capitol Technology University
 Kenneth Mayer 
 distlearn@captechu.edu 
 ken.i.mayer@gmail.com

 Addentum Copyright (C) 2019 Gabriel Sieben
*/

include '../common/int_config.php';

?>
<!doctype html>
<html>

<head>
  <title>Adobe Recordings Settings</title>
  <link rel="stylesheet" type="text/css" href="css/style.css">
</head>

<body>
  <?php
  $GLOBALS['qs'] = $_SERVER['QUERY_STRING'];

  include '../common/sess.php';
  include '../common/adobe.php';

  $query = mysqli_query($dbcon, "select * from cc_configdetails");
  while ($info = mysqli_fetch_array($query)) {
    $root_path = $info['root_path'];
    $number_of_instances = $info['number_of_instances'];
    $download_from_date = $info['download_from_date'];
  }
  if ($download_from_date == '0000-00-00') {
    $download_from_date = '';
  }

  ?>
  <div class="banner">
    <img id="bannerLogo" border="0" src="images/home.png" height="50" width="360">
    <div id="log" style="">
      <a href="index.php?<?php print $GLOBALS['qs']; ?>" class="menulink">Configuration</a>
      <a href="service.php?<?php print $GLOBALS['qs']; ?>" class="menulink">Repopulate</a>
      <a href="reports.php?<?php print $GLOBALS['qs']; ?>">Reports</a>
    </div>
  </div>
  <div class="content">
    <h3>Configuration parameters</h3>
    <table align="center" width="600">

      <tr>
        <td></td>
      </tr>
      <tr>
        <td></td>
      </tr>

      <form action="update.php?<?php print $GLOBALS['qs']; ?>" method="POST">
        <tr>
          <td>Path to save recordings:</td>
          <td> <input type="text" name="path" value='<?php print $root_path; ?>'></td>
        </tr>

        <tr>
          <td>Number of recordings:</td>
          <td> <input type="number" max="5" min="1" name="recordings" value='<?php print $number_of_instances; ?>'> Max:5</td>
        </tr>
        <tr>
          <td>From date:</td>
          <td> <input type="datetime" name="fromdate" value="<?php print $download_from_date ?>">
            Ex:yyyy-mm-dd<br />
            Leave blank or with 0000-00-00 to have the program automatically search one week before the
            current day. </td>
        </tr>
        <tr>
          <td></td>
          <td><input type="submit" value="Save" /></td>
        </tr>
      </form>
    </table>
  </div>
</body>

</html>