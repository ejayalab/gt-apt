<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php print get_string('sitename'); ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <?php
        if (is_array($CFG->jsArrayIncludes) and count($CFG->jsArrayIncludes)) {

            foreach ($CFG->jsArrayIncludes as $pathJsFile) {
                ?>
                <script src="<?php print $CFG->wwwroot . $pathJsFile; ?>" type="text/javascript"></script>
                <?php
            }
        }


        if (is_array($CFG->cssArrayIncludes) and count($CFG->cssArrayIncludes)) {

            foreach ($CFG->cssArrayIncludes as $pathCssFile) {
                ?>
                <link rel="stylesheet" type="text/css" href="<?php print $CFG->wwwroot . $pathCssFile; ?>" media="all">
                <?php
            }
        }
        ?>

    </head>
    <body>
        <div class="error">
            <p>
                <?php print 'Error fatal: Esto es una prueba'; ?>
            </p>
        </div>
    </body>
</html>