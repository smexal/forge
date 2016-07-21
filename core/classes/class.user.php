<?php

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

      $recipient = $user->get('email');
      $subject = sprintf(i('Activation Link for %s'), $user->get('username')). ' - '.
        Settings::get('title_'.Localization::getCurrentLanguage());

      $message = sprintf(i('Hello %s'), $user->get('username'))  . "\r\n" . "\r\n";
      $message.= sprintf(i('Click the following link to complete your account activation:')) . "\r\n";
      $message.= $user->getActivationLink() . "\r\n" . "\r\n" . "\r\n";
      $message.= sprintf(i('mail_end_text'));

      // für HTML-E-Mails muss der 'Content-type'-Header gesetzt werden
      $header  = 'MIME-Version: 1.0' . "\r\n";
      $header .= 'Content-type: text/plain; charset=utf-8' . "\r\n";

      // zusätzliche Header
      //$header .= 'From: Geburtstags-Erinnerungen <geburtstag@example.com>' . "\r\n";

      mail($recipient, $subject, $message, $header);
    }

    public function get($field) {
        if(array_key_exists($field, $this->data)) {
            return $this->data[$field];
        } else {
            if(in_array($field, $this->fields)) {
                $this->getData();
                if(array_key_exists($field, $this->data)) {
                    return $this->data[$field];
                } else {
                    Logger::error(sprintf(i("Queried field '%1$s' which does not exist")), $field);
                }
            }
        }
    }

    public function hasGroup($id){
      foreach($this->data['groups'] as $group_entry) {
        if($group_entry['groupid'] == $id) {
          return true;
        }
      }
      return false;
    }

    public static function delete($id) {
        if(Auth::allowed("manage.users.delete")) {
            if( $id == App::instance()->user->get('id')) {
                return false;
            } else {
                App::instance()->db->where('id', $id);
                App::instance()->db->delete('users');
                return true;
            }
        } else {
            return false;
        }
    }

    public function groups() {
        if(is_numeric($this->get('id'))) {
            $this->app->db->where('userid', $this->data['id']);
            $this->data['groups'] = $this->app->db->get('groups_users');
        } else {
            $this->data['groups'] = array();
        }
        return $this->data['groups'];
    }

    public function allowed($permission) {
        if(array_key_exists('permissions', $this->data) && array_key_exists($permission, $this->data['permissions'])) {
            return $this->data['permissions'][$permission];
        }
        $this->app->db->where('name', $permission);
        $permission = $this->app->db->getOne('permissions');
        $this->app->db->where('permissionid', $permission['id']);
        $groupsWithPermission = $this->app->db->get('permissions_groups');
        foreach($this->data['groups'] as $user_group) {
            foreach($groupsWithPermission as $db_group) {
                if($user_group['groupid'] == $db_group['groupid']) {
                    $this->data['permissions'][$permission['name']] = true;
                    return true;
                }
            }
        }
        $this->data['permissions'][$permission['name']] = false;
        return false;
    }

    public static function exists($userid) {
      $db = App::instance()->db;
      $db->where("id", $userid);
      $member = $db->getOne("users");
      if($member > 0) {
        return true;
      }
      return false;
    }

    public function setName($newName) {
      if(! Auth::allowed("manage.users.edit")) {
          return i("Permission denied to edit users.");
      }
      // check if user already has that given username.
      $this->app->db->where('id', $this->get('id'));
      $usr = $this->app->db->getOne('users');
      if($usr['username'] == $newName) {
        return true;
      }

      $nameStatus = self::checkName($newName);
      if($nameStatus !== true) {
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
      if(! Auth::allowed("manage.users.edit")) {
          return i("Permission denied to edit users.");
      }
      // check if user already has that given email.
      $this->app->db->where('id', $this->get('id'));
      $usr = $this->app->db->getOne('users');
      if($usr['email'] == $newMail) {
        return true;
      }

      $mailStatus = self::checkMail($newMail);
      if($mailStatus !== true) {
        return $mailStatus;
      }
      // update database
      $this->app->db->where('id', $this->get('id'));
      $this->app->db->update('users', array(
        'email' => $newMail
      ));
      return true;
    }

    public function setPassword($new_pw, $new_pw_rep) {
      if(! Auth::allowed("manage.users.edit")) {
          return i("Permission denied to edit users.");
      }
      $pwStatus = self::checkPassword($new_pw);
      if($pwStatus !== true) {
        return $pwStatus;
      }
      // update database
      if($new_pw !== $new_pw_rep) {
        return i('The given passwort and the repetition do not match.');
      }
      $this->app->db->where('id', $this->get('id'));
      $this->app->db->update('users', array(
        'password' => Utils::password($new_pw)
      ));
      return true;
    }

    public static function activateByHash($hash) {
      App::instance()->db->where('active', 0);
      $users = App::instance()->db->get('users', null, array("id", "email", "password"));
      foreach($users as $user) {
        if(md5($user['email'].$user['password']) == $hash) {
          App::instance()->db->where('id', $user['id']);
          App::instance()->db->update('users', array('active' => 1));
          return true;
        }
      }
      return false;
    }

    public static function getAll() {
      if(! Auth::allowed("manage.users")) {
        return array();
      }
      return App::instance()->db->get('users', null, array("id", "username", "email"));
    }

    public static function search($term) {
      if(! Auth::allowed("manage.users")) {
        return array();
      }
      App::instance()->db->where("username", $term."%", "LIKE");
      return App::instance()->db->get('users', null, array("id", "username", "email"));
    }

    public function getActivationLink() {
      $string = md5($this->get('email').$this->get('password'));
      return Utils::getAbsoluteUrlRoot().Utils::getUrl(array('user-verification', $string));
    }

    public static function create($name, $password, $email, $registration = false) {
        $pass = false;
        if(Settings::get('allow_registration') === 'on' && $registration) { 
          $pass = true;
        }
        if(! Auth::allowed("manage.users.add", true) && $pass !== true) {
            return false;
        }
        $mailStatus = self::checkMail($email);
        if($mailStatus !== true) {
          return $mailStatus;
        }
        $passwordStatus = self::checkPassword($password);
        if($passwordStatus !== true) {
          return $passwordStatus;
        }
        $nameStatus = self::checkName($name);
        if($nameStatus !== true) {
          return $nameStatus;
        }

        $data = array(
            'username' => $name,
            'password' => Utils::password($password),
            'email' => $email
        );
        App::instance()->db->insert('users', $data);
        return false;
    }

    public static function checkUser($data) {
        $errors = array();
        $userMessage = self::checkName($data['name']);
        $errors['name'] = false;
        if($userMessage !== true) {
            // ok for username
            $errors['name'] = $userMessage;
        }

        $emailMessage = self::checkMail($data['email']);
        $errors['email'] = false;
        if($emailMessage !== true) {
          $errors['email'] = $emailMessage;
        }
        
        $errors['password'] = false;
        if(!$data['password_repeat']) {
          $repeat = true;
        } else {
          $repeat = $data['password_repeat'];
        }
        $passwordMessage = self::checkPassword($data['password'], $repeat);
        if($passwordMessage !== true) {
          $errors['password'] = $passwordMessage;
        }

        return $errors;
    }

    private static function checkName($name) {
      $app = App::instance();
      if( strlen($name) <= 2 ) {
        return i('Username is too short.');
      }
      $app->db->where("username", $name);
      $app->db->get("users");
      if($app->db->count > 0) {
        return i("User with that name already exists.");
      }
      return true;
    }

    private static function checkMail($email) {
      $app = App::instance();
      if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          return i('Invalid e-mail address.');
      }
      $app->db->where("email", $email);
      $app->db->get("users");
      if($app->db->count > 0) {
        return i("User with that email address already exists.");
      }
      return true;
    }

    private static function checkPassword($password, $repeat = false) {
      if(strlen($password) <= 3) {
        return i('Given password is too short.');
      }
      if($repeat) {
        if($password !== $repeat) {
          return i('Given passwords do not match.');
        }
      }
      return true;
    }

}

?>
