<?php

namespace Forge\Core\Classes;

use \Forge\Core\Classes\Logger;
use \Forge\Core\App\App;
use \Forge\Core\App\Auth;
use \Forge\Core\App\ModifyHandler;
use \Forge\Core\Classes\Settings;

class User {
    private $app;
    private $data = false;
    private $fields = array(
        'id',
        'username',
        'email',
        'password',
        'active'
    );
    private static $avatarDirectory = UPLOAD_DIR.'avatar/';

    public function __construct($id) {
        $this->app = App::instance();
        $this->data['id'] = $id;
        $this->groups();
    }

    public function getData() {
        $this->app->db->where('id', $this->data['id']);
        $user = $this->app->db->getOne('users');
        foreach($this->fields as $field) {
            $this->data[$field] = $user[$field];
        }
    }

    public static function sendActivationLink($userID) {
        $user = new self($userID);

        $mail = new Mail();
        $mail->recipient($user->get('email'));

        $mail->subject(sprintf(i('Activation Link for %s'), $user->get('username')). ' - '.
            Settings::get('title_'.Localization::getCurrentLanguage()));

        $mail->addMessage(sprintf(i('Hello %s'), $user->get('username'))  . "\r\n" . "\r\n");
        $mail->addMessage(sprintf(i('Click the following link to complete your account activation:')) . "\r\n");
        $mail->addMessage($user->getActivationLink() . "\r\n" . "\r\n" . "\r\n");
        $mail->addMessage(sprintf(i('mail_end_text', 'core')));

        $mail->send();
    }

    public static function sendRecoveryMail() {
        // TODO: SEND IT!
        Logger::debug('Send recovery email.... nyi');
    }

    public function get($field) {
        if (array_key_exists($field, $this->data)) {
            return $this->data[$field];
        } else {
            if (in_array($field, $this->fields)) {
                $this->getData();
                if (array_key_exists($field, $this->data)) {
                    return $this->data[$field];
                } else {
                    Logger::error(sprintf(i("Queried field `%1$s` which does not exist", 'core')), $field);
                }
            }
        }
    }

    public function hasGroup($id){
        foreach ($this->data['groups'] as $group_entry) {
            if ($group_entry['groupid'] == $id) {
                return true;
            }
        }
        return false;
    }

    public static function delete($id) {
        $app = App::instance();
        if (Auth::allowed("manage.users.delete")) {
            if( $id == $app->user->get('id')) {
                return false;
            } else {
                self::deleteAvatar($id);
                $app->db->where('id', $id);
                $app->db->delete('users');

                ModifyHandler::instance()->trigger(
                    'core_delete_user', $id
                );
                return true;
            }
        } else {
            return false;
        }
    }

    public function groups() {
        if (is_numeric($this->get('id'))) {
            $this->app->db->where('userid', $this->data['id']);
            $this->data['groups'] = $this->app->db->get('groups_users');
        } else {
            $this->data['groups'] = array();
        }
        return $this->data['groups'];
    }

    public function allowed($permission) {
        if (array_key_exists('permissions', $this->data) && array_key_exists($permission, $this->data['permissions'])) {
            return $this->data['permissions'][$permission];
        }
        $this->app->db->where('name', $permission);
        $permission = $this->app->db->getOne('permissions');
        
        // There does not exist a permission with the provided name
        if(!$permission)
            return false;

        $this->app->db->where('permissionid', $permission['id']);
        $groupsWithPermission = $this->app->db->get('permissions_groups');
        
        foreach ($this->data['groups'] as $user_group) {
            foreach ($groupsWithPermission as $db_group) {
                if ($user_group['groupid'] == $db_group['groupid']) {
                    $this->data['permissions'][$permission['name']] = true;
                    return true;
                }
            }
        }
        $this->data['permissions'][$permission['name']] = false;
        return false;
    }

    public static function exists($user) {
        $app = App::instance();
        if (is_numeric($user)) {
            $db = $app->db;
            $db->where("id", $user);
            $member = $db->getOne("users");
            if(count($member) > 0) {
                return $user;
            }
        } else {
            if (Utils::isEmail($user)) {
                $db = $app->db;
                $db->where("email", $user);
                $member = $db->getOne("users");
                if (count($member) > 0) {
                    return $member['id'];
                }
            }
        }
        return false;
    }

    public function setName($newName) {
        if (! Auth::allowed("manage.users.edit") && App::instance()->user->get('id') != $this->get('id')) {
            return i("Permission denied to edit users.");
        }
        // check if user already has that given username.
        $this->app->db->where('id', $this->get('id'));
        $usr = $this->app->db->getOne('users');
        if ($usr['username'] == $newName) {
            return true;
        }

        $nameStatus = self::checkName($newName);
        if ($nameStatus !== true) {
            return $nameStatus;
        }
        // update database
        $this->app->db->where('id', $this->get('id'));
        $this->app->db->update('users', array(
            'username' => $newName
        ));
        return true;
    }

    public function setMail($newMail) {
        if (! Auth::allowed("manage.users.edit")) {
            return i("Permission denied to edit users.");
        }
        // check if user already has that given email.
        $this->app->db->where('id', $this->get('id'));
        $usr = $this->app->db->getOne('users');
        if ($usr['email'] == $newMail) {
            return true;
        }

        $mailStatus = self::checkMail($newMail);
        if ($mailStatus !== true) {
            return $mailStatus;
        }
        // update database
        $this->app->db->where('id', $this->get('id'));
        $this->app->db->update('users', array(
            'email' => $newMail
        ));
      return true;
    }

    private static function getAvatarName($id) {
        if(! $id)
            $id = $this->get('id');
        return 'avatar_user_'.md5($id);
    }

    private static function deleteAvatar($id) {
        // delete current images 
        $files = glob(self::$avatarDirectory.self::getAvatarName($id).'.*');
        if (count($files) > 0) {
            foreach ($files as $f) {
                unlink($f);
            }
        }
    }

    public function setAvatar($file) {
        // is not an image...
        if(! Media::_isImage($file['type'])) {
            return;
        }

        self::deleteAvatar($this->get('id'));

        $parts = explode(".", $file['name']);
        $ext = strtolower(array_pop($parts));

        $filename = self::getAvatarName($this->get('id')).".".$ext;

        if (!file_exists(self::$avatarDirectory)) {
            mkdir(self::$avatarDirectory, 0655, true);
        }

        $width = 100;
        $height = 100;
        if(Settings::get('forge_avatar_width')) {
            $width = Settings::get('forge_avatar_width');
        }
        if(Settings::get('forge_avatar_height')) {
            $height = Settings::get('forge_avatar_height');
        }

        if (move_uploaded_file($file['tmp_name'], self::$avatarDirectory.$filename)) {
            Utils::resizeImage(self::$avatarDirectory.$filename, self::$avatarDirectory.$filename, $width, $height);
            // continue....
        }
    }

    public function getAvatar($type="url") {
        $files = glob(self::$avatarDirectory.self::getAvatarName($this->get('id')).'.*');
        if (count($files) > 0) {
            foreach ($files as $file) {
                if($type == 'url') {
                    return UPLOAD_WWW.'avatar/'.basename($file);
                }
            }
        }
        return;
    }

    public function setPassword($new_pw, $new_pw_rep) {
        if (! Auth::allowed("manage.users.edit")) {
            return i("Permission denied to edit users.");
        }
        $pwStatus = self::checkPassword($new_pw);
        if ($pwStatus !== true) {
            return $pwStatus;
        }
        // update database
        if ($new_pw !== $new_pw_rep) {
            return i('The given passwort and the repetition do not match.');
        }
        $this->app->db->where('id', $this->get('id'));
        $this->app->db->update('users', array(
            'password' => Utils::password($new_pw)
        ));
        return true;
    }

    public static function activateByHash($hash) {
        $app = App::instance();
        $app->db->where('active', 0);
        $users = $app->db->get('users', null, array("id", "email", "password"));
        foreach ($users as $user) {
            if (md5($user['email'].$user['password']) == $hash) {
                $app->db->where('id', $user['id']);
                $app->db->update('users', array('active' => 1));
                return true;
            }
        }
        return false;
    }

    public static function getAll() {
        $app = App::instance();
        if (! Auth::allowed("manage.users", true)) {
            return array();
        }
        return $app->db->get('users', null, array("id", "username", "email"));
    }

    public static function search($term) {
        $app = App::instance();
        if (! Auth::allowed("manage.users")) {
            return array();
        }
        $app->db->where("username", $term."%", "LIKE");
        return $app->db->get('users', null, array("id", "username", "email"));
    }

    public function getActivationLink() {
        $string = md5($this->get('email').$this->get('password'));
        return Utils::getAbsoluteUrlRoot().Utils::getUrl(array('user-verification', $string));
    }

    public static function create($name, $password, $email, $registration = false) {
        $app = App::instance();
        $pass = false;
        if (Settings::get('allow_registration') === 'on' && $registration) {
            $pass = true;
        }
        if (! Auth::allowed("manage.users.add", true) && $pass !== true) {
            return false;
        }
        $mailStatus = self::checkMail($email);
        if ($mailStatus !== true) {
            return $mailStatus;
        }
        $passwordStatus = self::checkPassword($password);
        if ($passwordStatus !== true) {
            return $passwordStatus;
        }
        $nameStatus = self::checkName($name);
        if ($nameStatus !== true) {
          return $nameStatus;
        }

        $active = 0;
        if(Auth::allowed("manage.users.add", true)) {
            $active = 1;
        }

        $data = array(
            'username' => $name,
            'password' => Utils::password($password),
            'email' => $email,
            'active' => $active
        );
        $app->db->insert('users', $data);
        return false;
    }

    public static function checkUser($data) {
        $errors = array();
        $userMessage = self::checkName($data['name']);
        $errors['name'] = false;
        if ($userMessage !== true) {
            // ok for username
            $errors['name'] = $userMessage;
        }

        $emailMessage = self::checkMail($data['email']);
        $errors['email'] = false;
        if ($emailMessage !== true) {
            $errors['email'] = $emailMessage;
        }

        $errors['password'] = false;
        if (!$data['password_repeat']) {
            $repeat = true;
        } else {
            $repeat = $data['password_repeat'];
        }
        $passwordMessage = self::checkPassword($data['password'], $repeat);
        if ($passwordMessage !== true) {
            $errors['password'] = $passwordMessage;
        }

        return $errors;
    }

    private static function checkName($name) {
        $app = App::instance();
        if (strlen($name) <= 2) {
            return i('Username is too short.');
        }
        $app->db->where("username", $name);
        $app->db->get("users");
        if ($app->db->count > 0) {
            return i("User with that name already exists.");
        }
        return true;
    }

    private static function checkMail($email) {
        $app = App::instance();
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return i('Invalid e-mail address.');
        }
        $app->db->where("email", $email);
        $app->db->get("users");
        if ($app->db->count > 0) {
            return i("User with that email address already exists.");
        }
        return true;
    }

    private static function checkPassword($password, $repeat = false) {
        if (strlen($password) <= 3) {
            return i('Given password is too short.');
        }
        if ($repeat) {
            if ($password !== $repeat) {
                return i('Given passwords do not match.');
            }
        }
        return true;
    }
}

