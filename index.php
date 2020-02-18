<?php
namespace GitAllie;
session_start();

include_once 'gitHubController.php';
include_once 'gitHubView.php';
$gitHub = new gitHubCommander();

/**
 * Set things
 */
// moved to ajax.php -- $gitHub->set_issue();

/**
 * View things.
 */
$base = new Base($gitHub);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Swordfish Application Test</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php
print $base;
?>
<form>
    <label>
        <span>Title</span>
        <input type="text" name="issue-name" placeholder="Name">
    </label>
    <label>
        <span>Labels</span>
        <select name="labels[]" multiple>
            <?php
            foreach($gitHub->labels as $value) {
                $label_convention = new Label($value);
                $key = $label_convention->type;
                $view_labels[$key][] = $label_convention;
            }

            foreach ($view_labels as $key => $view_label_type) {
                //         print "<option value='1' name='' style='color:\#{$value->color}'>{$value->name} ({$type})</option>";
                print "<optgroup label='$key'>";
                foreach ($view_label_type as $label) {
                    print "<option value='{$label->name}'>{$label->name}</option>";
                }
                print "</optgroup>";
            }
            ?>
        </select>
    </label>
    <label>
        <span>Description</span>
        <textarea name="issue-description" placeholder="Description"></textarea>
    </label>
    <label>
        <span>Save</span>
        <input type="submit">
    </label>
</form>
