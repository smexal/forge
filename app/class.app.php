<?php
session_start();

class App {
    public $db = null;
    public $eh = null;
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
      I18N::instance();

      ob_start();
      if(isset($_POST['event'])) {
        $this->eh->trigger($_POST['event'], $_POST);
      }
      echo $this->render(TEMPLATE_DIR, "layout", array(
          "head" => $this->header(),
          "content" => $this->content(),
          "sticky" => $this->sticky
      ));
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
      $vm = new ViewManager();
      $found = false;
      $load_main = $base_view == '' ? true : false;
      foreach($vm->views as $view) {
        $rc = new ReflectionClass($view);
        
        if($rc->isAbstract())
          continue;
        $instance = 'instance';
        $view = $view::$instance();
        // tryed to load subview as main view.
        if($view->parent !== false)
          continue;

        if($load_main && $view->default || $base_view == $view->name()) {
          $found = true;
          break;
        }
      }
      if(!$found) {
        Logger::error("View not found.");
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
      if($go_back)
        $_SESSION['back'] = $go_back;
      if(!$go_back && isset($_SESSION['back']))
        unset($_SESSION['back']);
      
      if(is_array($target)) {
        header("Location: ".WWW_ROOT.implode("/", $target));
      } else {
        header("Location: ".WWW_ROOT.$target);
      }
    }
    public function redirectBack() {
      if(isset($_SESSION['back'])) {
        $back = $_SESSION['back'];
        unset($_SESSION['back']);
        $this->redirect($back);
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


    private function __construct(){}
    private function __clone(){}
}

?>