<?php
namespace GitAllie;
session_start();

include_once 'gitHubController.php';
include_once 'gitHubView.php';
$gitHub = new gitHubCommander();

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
    <script
            src="https://code.jquery.com/jquery-3.4.1.js"
            integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU="
            crossorigin="anonymous"></script>
    <script>
        jQuery(document).ready(function ($) {
            $('form').on('submit', function (e, v) {
                e.preventDefault();
                $.post('ajax.php', $(this).serialize());
            })
        });
    </script>
</head>
<body>
<?php
print $base;
?>
<form method="post">
    <label>
        <span>Title</span>
        <input type="text" name="issue-name" required placeholder="Name">
    </label>
    <label>
        <span>Labels</span>
        <select name="labels[]" multiple>
            <?php
            foreach ($gitHub->labels as $value) {
                $label_convention = new Label($value);
                $key = $label_convention->type;
                $view_labels[$key][] = $label_convention;
            }

            foreach ($view_labels as $key => $view_label_type) {
                //         print "<option value='1' name='' style='color:\#{$value->color}'>{$value->name} ({$type})</option>";
                print "<optgroup label='$key'>";
                foreach ($view_label_type as $label) {
                    print "<option value='{$label->name_raw}'>{$label->name}</option>";
                }
                print "</optgroup>";
            }
            ?>
        </select>
    </label>

    <!--new collaborators-->
    <label>
        <span>Assignees</span>
        <select name="assignees[]" multiple>
            <?php
            foreach ($gitHub->collaborators as $value) {
                $user_convention = new User($value);
                $view_users[$user_convention->login] = $user_convention->login;
            }

            foreach ($view_users as $key => $user_name) {
                print "<option value='{$key}'>{$user_name}</option>";

            }
            ?>
        </select>
    </label>
    <!--new-end-->


    <label>
        <span>Description</span>
        <textarea name="issue-description" required placeholder="Description"></textarea>
    </label>
    <label>
        <span>Save</span>
        <input type="hidden" name="action" value="create-issue">
        <input type="submit">
    </label>
</form>
