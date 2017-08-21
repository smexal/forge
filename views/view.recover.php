<?php

namespace Forge\Views;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\App\Auth;
use \Forge\Core\Classes\Fields;
use \Forge\Core\Classes\User;
use \Forge\Core\Classes\Utils;
use \Forge\Core\Classes\Settings;



class RecoverView extends View {
    public $name = 'recover';
    public $allowNavigation = true;
    public $events = array(
        "onRecoverFormSubmit"
    );

    private $errors = array();
    private $data = array();

    public function content($parts = array()) {
        if(Settings::get('allow_registration')) {
            return $this->getRecoverForm();
        } else {
            App::instance()->redirect(array('denied'));
        }
    }

    public function onRecoverFormSubmit() {
        
        // send recover link....
        $this->data = $_POST;

        if(! Utils::isEmail($this->data['email'])) {
            $this->errors['email'] = i('This does not look like an e-Mail address.', 'core');
        } else {
            User::sendRecoveryMail();
            App::instance()->addMessage(sprintf(i('The recovery e-mail has been sent to %1$s'), $this->data['email']), "success");
            App::instance()->redirect([]);
        }
    }

    public function getRecoverForm() {
        if(! Settings::get('allow_registration')) {
            App::instance()->redirect(['denied']);
        }
        $return = '';
        $return.= Fields::hidden(array(
            "name" => "event",
            "value" => "onRecoverFormSubmit"
        ));
        $return.= Fields::text(array(
            'key' => 'email',
            'label' => i('E-Mail'),
            'autocomplete' => false,
            'error' => @$this->errors['email']
        ), @$this->data['email']);
        $return.= Fields::button(i('Send Recovery Link', 'core'));

        $return = App::instance()->render(CORE_ROOT."ressources/templates/assets/", "form", array(
                "method" => 'post',
                "action" => '',
                "ajax" => false,
                'horizontal' => false,
                'ajax_target' => '',
                'content' => [$return]
        ));
        return App::instance()->render(CORE_TEMPLATE_DIR.'views/sites/', 'smallcenter-content', [
            'title' => i('Password Recovery', 'core'),
            'lead' => i('You can send yourself an E-Mail with a link to set a new password with this form.', 'core'),
            'content' => $return
        ]);

    }
}
