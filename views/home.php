<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php print get_string('sitename');?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <?php
        foreach ($jsArrayIncludes as $pathJsFile) {
            ?>
        <script src="<?php print $CFG->wwwroot . $pathJsFile;?>" type="text/javascript"></script>
            <?php
        }
        
        foreach ($cssArrayIncludes as $pathCssFile) {
            ?>
        <link rel="stylesheet" type="text/css" href="<?php print $CFG->wwwroot . $pathCssFile;?>" media="all">
            <?php
        }
        ?>
    </head>
    <body>
        <div>TODO write content</div>
    </body>
</html>