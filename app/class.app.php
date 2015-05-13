<?php

class App {
    public $db = null;
    public $eh = null;
    public $vm = null;
    public $user = null;
    public $sticky = false;
    static private $instance = null;

    static public function instance() {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        Loader::instance()->prepare();
        return self::$instance;
    }

    public function run() {

      if(is_null($this->eh)){
        $this->eh = EventHandler::instance();
      }
      if(is_null($this->db)) {
        $this->db = new MysqliDb(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
      }
      if(is_null($this->vm)) {
        $this->vm = new ViewManager();
      }
      I18N::instance();
      Auth::setSessionUser();


      ob_start();
      foreach($this->vm->views as $view) {
        $view = $view::instance();
        $this->eh->add($view->events);
      }
      if(isset($_POST['event'])) {
        $this->eh->trigger($_POST['event'], $_POST);
      }
      if(Utils::isAjax()) {
        echo $this->render(TEMPLATE_DIR, "layout.ajax", array(
          "content" => $this->content(),
          "messages" => $this->displayMessages()
        ));
      } else {
        echo $this->render(TEMPLATE_DIR, "layout", array(
          "head" => $this->header(),
          "content" => $this->content(),
          "messages" => $this->displayMessages(),
          "sticky" => $this->sticky
        ));
      }
      ob_end_flush();
    }

    public function header() {
      $loader = Loader::instance();
      return $this->render(TEMPLATE_DIR, "head", array(
        'scripts' => $loader->getScripts(),
        'styles' => $loader->getStyles()
      ));
    }

    public function content() {
      $uri_components = Utils::getUriComponents();
      $this->addFootprint($uri_components);
      $base_view = $uri_components[0];
      $found = false;
      $load_main = $base_view == '' ? true : false;
      foreach($this->vm->views as $view) {
        $instance = 'instance';
        $view = $view::$instance();
        // tried to load subview as main view.
        if($view->parent !== false)
          continue;

        if($load_main && $view->default || $base_view == $view->name()) {
          $found = true;
          break;
        }
      }
      if(!$found) {
        Logger::error("View '".Utils::getUrl($uri_components)."' not found.");
        $this->redirect('404');
      } else {
        $view->initEssential();
        array_shift($uri_components);
        return $view->content($uri_components);
      }
    }


    public function render($template_dir, $template_file, $args=array()) {
      if(!class_exists('RainTPL')) {
        Logger::error("RainTPL library not loaded.");
      }
      $config = array(
        "tpl_dir"       => $template_dir,
        "cache_dir"     => $template_dir."cache/",
        "path_replace"  => false
      );
      RainTPL::configure( $config );
      $tpl = new RainTPL();
      foreach($args as $key => $value)
        $tpl->assign($key, $value);
      return $tpl->draw($template_file, true);
    }

    public function redirect($target, $go_back=false) {
      if(is_array($target)) {
        $target = WWW_ROOT.implode("/", $target);
      } else {
        if(!strstr($target, WWW_ROOT)) {
          $target = WWW_ROOT.$target;
        }
      }
      if($go_back) {
        $_SESSION['back'] = $go_back;
      }
      if(!$go_back && isset($_SESSION['back'])) {
        unset($_SESSION['back']);
      }

      if(Utils::isAjax()) {
        exit(json_encode(array(
          "action" => "redirect",
          "target" => $target
        )));
      } else {
        exit(header("Location: ".$target));
      }
    }
    public function redirectBack() {
      if(isset($_SESSION['back'])) {
        $this->redirect($_SESSION['back']);
      } else {
        $this->redirect('');
      }
    }

    public function addFootprint($uri_components) {
      if(!isset($_SESSION['footprint'])) {
        $_SESSION['footprint'] = array();
      }
      // if the site is the same as before, don't set print
      if(count($_SESSION['footprint']) > 0)
        if($_SESSION['footprint'][count($_SESSION['footprint'])-1] == $uri_components)
          return;

      while(count($_SESSION['footprint']) > FOOTPRINT_SIZE) {
        array_shift($_SESSION['footprint']);
      }
      array_push($_SESSION['footprint'], $uri_components);
    }

    public function addMessage($message, $type="warning") {
      if(!isset($_SESSION['messages'])) {
        $_SESSION['messages'] = array();
      }
      array_push($_SESSION['messages'], array(
        "text" => $message,
        "type" => $type
      ));
    }

    public function displayMessages() {
      if(! array_key_exists('messages', $_SESSION)) {
        return false;
      }
      $count = 0;
      foreach($_SESSION['messages'] as $message) {
        $count++;
      }
      if($count > 0) {
        $data = $_SESSION['messages'];
        unset($_SESSION['messages']);
        return $data;
      } else {
        return false;
      }
    }


    private function __construct(){}
    private function __clone(){}
}

?>
