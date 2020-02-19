<?php
namespace GitAllie;
session_start();
include_once 'gitHubController.php';


class ajax extends gitHubCommander {
    public function __construct()
    {
        parent::__construct();

        /**
         * Set things
         */
        if (isset($_POST)) {
            //Set things mode
            if (isset($_POST['action']) && !empty($_POST['action'])) {
                if($_POST['action'] === 'create-issue') {
                    $this->set_issue();
                }
            }
        }
    }
}

new ajax();