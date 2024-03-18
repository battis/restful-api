<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Authorize</title>
    </head>
    <body>
        <h1>Authorize <?= $client->getName() ?>?</h1>
        <p><?= $client->getDescription() ?></p>
        <p><?= $client->getName() ?> requests the following scopes:</p>
        <dl>
            <?php foreach ($scopes as $scope) {
                echo "<dt>{$scope->getIdentifier()}</dt>";
                echo "<dd>{$scope->getDescription()}</dd>";
            } ?>
        </dl>
        <form method="get" action="authorize">
            <?php foreach ($_GET as $key => $value) {
                echo "<input type=\"hidden\" name=\"$key\" value=\"$value\"/>";
            } ?>
            <button type="submit" name="authorize" value="yes">Yes</button>
            <button type="reset" name="authorize" value="no">No</button>
        </form>
    </body>
</html>
