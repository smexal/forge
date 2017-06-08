<?php

/** SIMPLE FORGE INSTALLATION SCRIPT 
    the folder containing this script can be deleted after forge is up and running.
*/

$installation = new Installation();

if(array_key_exists('install-now', $_POST) && $installation->ready()) {
    $installation->start();
} else {
    $installation->form();
}


class Installation {
    private $errors = [];

    public function start() {
        $mysqli = new mysqli($_POST['db_host'], $_POST['db_username'], $_POST['db_password'], $_POST['db_name']);

        // create database tables with sql file.
        $sqlQueries = file_get_contents('install-database.sql');
        if ($mysqli->multi_query($sqlQueries)) {
            do {
                if ($result = $mysqli->store_result()) {
                    while ($row = $result->fetch_row()) {}
                    $result->free();
                }
                if ($mysqli->more_results()) {}
            } while (@$mysqli->next_result());
        }

        // check if admin user table. - demm hekceries
        $sql= "SELECT active FROM users";
        $result = $mysqli->query($sql);
        if($result->num_rows == 0) {

            // insert user
            $sql = "INSERT INTO `users` (username, email, password, active) ";
            $sql.= "VALUES ('".$_POST['admin_username']."', '".$_POST['admin_email']."', '".password_hash($_POST['admin_password'], PASSWORD_BCRYPT)."', 1)";
            $mysqli->query($sql);
            $userId = $mysqli->insert_id;

            // add a language
            $lang = $this->getUserBrowserLanguage();
            $sql ="INSERT INTO `languages` (`id`, `code`, `name`, `default`) VALUES (NULL, '".$lang."', '<".$lang.">', '0')";
            $mysqli->query($sql);

            // add basic permissions
            $permissions = ['manage', 'manage.users', 'manage.groups', 'manage.permissions'];
            $permissionIds = [];
            foreach($permissions as $p) {
                $sql = "INSERT INTO `permissions` (name) VALUES ('".$p."')";
                $mysqli->query($sql);
                $permissionIds[] = $mysqli->insert_id;
            }

            // add a "Administrators" group
            $sql = "INSERT INTO `groups` (name) VALUES ('Administrators')";
            $mysqli->query($sql);
            $groupId = $mysqli->insert_id;

            // add the permissions to the administrator group
            foreach($permissionIds as $perm) {
                $sql = "INSERT INTO `permissions_groups` (groupid, permissionid) VALUES (".$groupId.", ".$perm.")";
                $mysqli->query($sql);
            }

            // add the user to the group.
            $sql = "INSERT INTO `groups_users` (groupid, userid) VALUES(".$groupId.", ".$userId.")";
            $mysqli->query($sql);

            // all done.

        } // else skip user creation...
        
        echo '<p>Installation completed.<br />Login on "/manage" with your credentials.</p>';
    }

    public function ready() {
        $mysqli = new mysqli($_POST['db_host'], $_POST['db_username'], $_POST['db_password'], $_POST['db_name']);

        if ($mysqli->connect_error) {
            $this->errors[] = 'Unable to connect to database';
        }

        $admin_checks = ['admin_email', 'admin_username', 'admin_password'];
        foreach($admin_checks as $check) {
            if(! array_key_exists($check, $_POST)) {
                $this->errors[] = "Missing admin user Information: ".$_POST[$check];
            } else {
                if(strlen($_POST[$check]) < 3) {
                    $this->errors[] = $check.' is too short.';
                }
            }
        }

        if(count($this->errors) > 0) {
            return false;
        }
        return true;

    }


    public function form() {
        echo '<html>';
        echo '<head>';
        echo '<style>';
        echo 'label { display: inline-block; min-width: 150px; }';
        echo 'label, input { margin-bottom: 10px }';
        echo '</style>';
        echo '</head>';
        echo '<body>';
        echo '<h1>Forge Installation</h1>';
        if(count($this->errors)) {
            foreach($this->errors as $error) {
                echo '<p style="color:#FF0011">'.$error.'</p>';
            } 
        }
        echo '<form method="post" action="">';
        echo '<div style="margin-bottom: 20px;">';
        echo '<small>This data will be <strong>not saved</strong><br />Insert this data aswell in the config.php File..</small>';
        echo '</div>';

        echo '<h3>Database</h3>';

        echo '<label for="db_username">Username: </label>';
        echo '<input type="text" value="'.@$_POST['db_username'].'" id="db_username" name="db_username"><br />';

        echo '<label for="db_password">Password: </label>';
        echo '<input type="text" value="'.@$_POST['db_password'].'" id="db_password" name="db_password"><br />';

        echo '<label for="db_name">Name: </label>';
        echo '<input type="text" value="'.@$_POST['db_name'].'" id="db_name" name="db_name"><br />';

        echo '<label for="db_host">Host: </label>';
        echo '<input type="text" value="'.@$_POST['db_host'].'" id="db_host" name="db_host"><br />';

        echo '<h3>Admin User</h3>';

        echo '<label for="admin_email">E-Mail: </label>';
        echo '<input type="email" value="'.@$_POST['admin_email'].'" id="admin_email" name="admin_email"><br />';

        echo '<label for="admin_username">Username: </label>';
        echo '<input type="text" value="'.@$_POST['admin_username'].'" id="admin_username" name="admin_username"><br />';

        echo '<label for="admin_password">Password: </label>';
        echo '<input type="text" value="'.@$_POST['admin_password'].'" id="admin_password" name="admin_password"><br />';

        echo '<label></label>';
        echo '<input type="submit" name="install-now" value="Start installation" />';

        echo '</form>';
        echo '</body>'; 
        echo '</html>';
    }

    public function getUserBrowserLanguage() {
        if (!($list = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']))) {
            return 'en';
        }
        if (preg_match_all('/([a-z]{1,8}(?:-[a-z]{1,8})?)(?:;q=([0-9.]+))?/', $list, $list)) {
            $res = array_combine($list[1], $list[2]);
            $lang = false;
            $prio = 0;
            foreach ($res as $n => $v) {
                $n = substr($n, 0, 2);
                $v = +$v ? +$v : 1;
                if ((!$lang || $v > $prio)) {
                    $prio = $v;
                    $lang = $n;
                }
            }
            if ($lang) {
                return $lang;
            }
        }
    }

}


?>