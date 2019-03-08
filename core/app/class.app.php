<?php

namespace Forge\Core\App;

use \Forge\Core\Classes\Logger;
use \Forge\Core\Classes\Utils;
use \Forge\Core\Classes\Cache;
use \Forge\Core\App\Api\Collection as CollectionAPI;

use \Forge\Core\App\Autoregister;

use \Forge\Loader;

class App {
    public $db = null;
    public $eh = null;
    public $vm = null;
    public $cm = null;
    public $mm = null;
    public $rd = null;
    public $nm = null;
    public $tm = null;
    public $mim = null;
    public $com = null;
    public $user = null;
    public $stream = false;
    public $sticky = false;
    public $page = false;

    private $prepared = false;
    private $uri_components = false;

    static private $instance = null;

    static public function instance() {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        Loader::instance()->prepare();
        return self::$instance;
    }

    public function setUri($components = array()) {
        if(!is_array($components)) {
            return;
        }
        $this->uri_components = $components;
    }

    private function managers() {
        /* API */
        CollectionAPI::instance()->register();

        if(is_null($this->eh)){
            $this->eh = EventHandler::instance();
        }

        if(is_null($this->mm)) {
            $this->mm = new ModuleManager();
        }

        if(is_null($this->rd)) {
            $this->rd = new RelationDirectory();
        }

        if(is_null($this->mim)){
            $this->mim = new MigrationManager();
        }

        if(is_null($this->tm)) {
            $this->tm = new ThemeManager();
        }

        if(is_null($this->nm)) {
            $this->nm = new NavigationManager();
        }
        // Has to be called after module and theme manager instantiiated
        Autoregister::autoregister();
        $this->mim->start();

        // start all active modules
        $this->mm->start();
        \fireEvent('onModulesLoaded');

        $timer = Logger::timer();


        if(is_null($this->vm)) {
            $this->vm = new ViewManager();
        }
        \fireEvent('onViewManagerLoaded');

        // init theme
        if($this->tm->theme !== '') {
            $this->tm->theme->start();
        } else {
            Logger::debug('No Theme set.');
        }

        if(is_null($this->com)) {
            $this->com = new ComponentManager();
        }

        Logger::debug('ComponentManager start');
        Logger::stop($timer);

        
        if(is_null($this->cm)) {
            $this->cm = new CollectionManager();
        }

        Logger::debug('CollectionManager start');
        Logger::stop($timer);

        // Collects relations (dependency on CollectionManager)
        $this->rd->start();

        Logger::debug('rd start');
        Logger::stop($timer);

        \fireEvent('onManagersLoaded');
    }

    /**
     * Allow the the instantiations of the managers
     * inside a PhpUnit-Test and prevent multiple callings
     * of the method
     */
    public function prepare() {
        if($this->prepared)
            return;

        if(is_null($this->db)) {
            $this->db = new \MysqliDb(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        }

        Auth::setSessionUser();

        $this->managers();

        $this->prepared = true;
    }

    public function run() {

        if( Cache::valid(Utils::getCurrentUrl()) ) {
            echo Cache::get(Utils::getCurrentUrl());
            exit();
        }

        \fireEvent('onAppRun');
        $this->prepare();

        $this->uri_components = Utils::getUriComponents();
        $this->addFootprint($this->uri_components);

        $base_view = '';
        if (is_array($this->uri_components) && array_key_exists(0, $this->uri_components))
            $base_view = $this->uri_components[0];

        $requiredView = false;
        $load_main = $base_view == '' ? true : false;

        Loader::instance()->manageStyles();

        $defaultView = false;

        foreach($this->vm->views as $view) {
            $view = $view::instance();
            $this->eh->add($view->events);
            // tried to load subview as main view.
            if($view->parent !== false)
                continue;
            if($view->default) {
                $defaultView = $view;
            }
            if($load_main && $view->default || $base_view == $view->name()) {
                $requiredView = $view;
                // TODO: This break breaks all events on any views
                // thus making backend saving impossible
                // See: Forge\Core\Views\Manage\Builder\Pages\EditelementView event onUpdateContentElement
                //break;
            }
        }

        if(!$requiredView) {
            $requiredView = $defaultView;
        }
        if(isset($_POST['event'])) {
            $this->eh->trigger($_POST['event'], $_POST);
        }
        $this->displayView($requiredView);
        \fireEvent('onFinishRun');
    }

    public function displayView($view) {
        ob_start();
        if($view->standalone) {
            echo $this->content($view);
        } else if(Utils::isAjax()) {
            echo $this->render(CORE_TEMPLATE_DIR, "layout.ajax", array(
                "content" => $this->content($view),
                "messages" => $this->displayMessages()
            ));
        } else {
            $parts = Utils::getUriComponents();
            // if has manage parts
            if(in_array("manage", $parts)) {
                echo $this->render(CORE_TEMPLATE_DIR, "layout", array(
                    "head" => $this->header($view),
                    "content" => $this->content($view),
                    "messages" => $this->displayMessages(),
                    "sticky" => $this->sticky
            ));
            // else render with theme layout
            } else {
                echo $this->renderViewInTheme($view);
            }
        }
        Cache::write(Utils::getCurrentUrl(), ob_get_contents());
        if(ob_get_level() > 0) {
            ob_end_flush();
        }
    }

    private function renderViewInTheme($view) {
        // run theme methods..
        if($this->tm->theme !== '') {
            $this->tm->theme->styles();
        }

        $head = '';
        if($this->tm->theme !== '') {
            $head = $this->tm->theme->header();
        }

        $globals = array();
        if($this->tm->theme !== '') {
            $globals = $this->tm->theme->globals();
        }
        return $this->render($this->tm->getTemplateDirectory(), "layout", array_merge(
            array(
                'head' => $head,
                'bodyclass' => '',
                'body' => $this->content($view),
                'messages' => $this->displayMessages()
            ),
            $globals
        ));
    }

    public function header($view) {
        $loader = Loader::instance();
        return $this->render(CORE_TEMPLATE_DIR, "head", array(
            'title' => $this->getTitle($view),
            'defered_scripts' => [],
            'scripts' => $loader->getScripts(),
            'styles' => $loader->getStyles(),
            'favicon' => $this->getFavicon($view),
            'defered_scripts' => [],
            'custom' => false
        ));
    }

    public function getFavicon($view) {
        return $view->favicon;
    }

    public function getTitle($view) {
        return $view->title();
    }

    public function content($view) {
        $view->initEssential();
        array_shift($this->uri_components);
        return $view->content($this->uri_components);
    }

    public function render($template_dir, $template_file, $args=array()) {
        if(!class_exists('RainTPL')) {
            Logger::error("RainTPL library not loaded.");
        }
        $template_dir .= substr($template_dir, -1) != '/' ? '/' : '';
        $config = array(
            "tpl_dir"       => $template_dir,
            "cache_dir"     => $template_dir."cache/",
            "path_replace"  => false
        );
        \RainTPL::configure( $config );
        $tpl = new \RainTPL();
        foreach($args as $key => $value)
            $tpl->assign($key, $value);
        return $tpl->draw($template_file, true);
    }

    public function redirect($target, $go_back=false, $forceAjaxThrough = false) {
        if(is_array($target)) {
            $target = Utils::getUrl($target);
        } else {
            if(!strstr($target, WWW_ROOT)) {
                $target = WWW_ROOT.$target;
            }
        }
        if($go_back) {
            $_SESSION['back'] = $go_back;
        }
        if(Utils::isAjax() && ! $forceAjaxThrough) {
            exit(json_encode(array(
                "action" => "redirect",
                "target" => $target
            )));
        } else {
            if($target == '/') {
                exit(header("Location: ".$target));
            }
            exit(header("Location: ".rtrim($target, "/")));
        }
    }

    public function refresh($target, $content, $update=false) {
        if(Utils::isAjax()) {
            exit(json_encode(array(
                "action" => $update ? "update" : "refresh",
                "target" => $target,
                "content" => $content
            )));
          } else {
              Logger::debug("Tryed to refresh without ajax. Target:".$target);
              // cannot refresh particular content without ajax.
          }
    }

    public function updatePart($target, $content) {
        $this->refresh($target, $content, true);
    }

    public function redirectBack() {
        if(array_key_exists('redirect', $_REQUEST)) {
            $this->redirect($_REQUEST['redirect']);
        }
        if(isset($_SESSION['back'])) {
            $back = $_SESSION['back'];
            unset($_SESSION['back']);
            Logger::debug('back');
            $this->redirect($back);
        } else {
            $this->redirect(Utils::getHomeUrl());
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

    public function stream($start = false) {
        if($start) {
            $this->stream = true;
        } else {
            $this->stream = false;
        }
    }

    public function streamActive() {
        return $this->stream;
    }

    public function getThemeDirectory() {
        if($this->tm->theme) {
            return $this->tm->theme->directory();
        } else {
            return '';
        }
    }

    public function getUser() {
      return $this->user;
    }

    public function setUser($user) {
      $this->user = $user;
    }

    private function __construct(){}
    private function __clone(){}
}
