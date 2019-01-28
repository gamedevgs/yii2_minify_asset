<?php
/**
 * Created by PhpStorm.
 * User: nam
 * Date: 03/01/2019
 * Time: 08:17
 */

use yii\helpers\Url;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Bootstrap Example</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>


<style>
    body {
        background-color: rgb(26, 117, 115);
    }
</style>
<body>

<div class="container">
    <h2>Bordered Table</h2>

    <form action="<?php echo Url::toRoute('build-assets/'); ?>" method="post">
        App id <input type="text" name="app_id" value="<?= $app_id ="2"; ?>">
        version <input type="text" name="v" value="<?php echo $version="5";?>">
        <input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>" value="<?= Yii::$app->request->csrfToken; ?>" />
       TOken
        <input type="text" name="token" value="<?= $token = md5($app_id.$version.date('h:i').'1'.'2');?>" />
        <button type="submit"> Save </button>
        <br>
        <?php echo "app=".$app_id;
                echo "<br>";
                echo date('h:i');
                echo "<br>";
            echo "v".$version;
        ?>
    </form>


</body>
</html>
