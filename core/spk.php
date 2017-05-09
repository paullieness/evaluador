<?php
/**{v=0.0.2a}**/
/* * **************************************************************************/
//
//      -------------------------------------------------------------------
//      |                 Sparky devCode 0.0.2a "Sparky"                  |
//      |   Copyright (C) 2016  Marcelo M. Montevideo, Uruguay            |
//      |                 https://twitter.com/Mnosh                       |
//      |                 https://github.com/mnshdev                      |
//      |                   marcelo23.m@gmail.com                         |
//      -------------------------------------------------------------------
//      
// -----------------------------------------------------------------------------
//  This program is free software; you can redistribute it and/or modify
//  it under the terms of the GNU Lesser General Public License as published
//  by the Free Software Foundation; either version 2 of the License, or
//  (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU Lesser General Public License for more details.
//
//  You should have received a copy of the GNU Lesser General Public License 
//  along with this program; if not, write to the Free Software Foundation, Inc.,
//  51 Franklin Street, Fifth Floor, Boston, MA 02110â€’1301 USA.1
// -----------------------------------------------------------------------------
//
/* * **************************************************************************/

namespace core;

//require(dirname(__FILE__) . '/_config/spkConfig.core.php');

class Spk {
    /* @var $instance Spk */

    private static $instance;           // singleton para el framework
    /* @var $page Page */
    private $page;                      // objeto page con la logica del mvc
    private $urlParams = array();
    private $views = array();

    /*
      private $db;                        // objeto de base de datos
      private $session;                   // objeto page con la logica del mvc
      private $devClases = array();       // conjunto de instancias, método load()
      private $devPlugs = array();        // conjunto de instancias de plugins
      private $theme;
      private $image;
     */

    /**
     * 
     * @return Spk
     */
    public static function getInstance() {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {


        $get = filter_input_array(INPUT_GET);
        $post = filter_input_array(INPUT_POST);
        if (!empty($post)) {
            foreach ($post as $k => $v) {
                $this->urlParams['post'][$k] = $v;
            }
        }
        if (!empty($get)) {
            foreach ($get as $k => $v) {
                $this->urlParams['get'][$k] = $v;
            }
        }
    }

    /**
     * 
     * @return Page
     */
    public function getPage() {
        if (!$this->page instanceof Page) {
            $this->page = new Page();
        }
        return $this->page;
    }

    /**
     * Se tiene una página cargada.
     * @return boolean
     */
    public function pageLoaded() {
        return ($this->page instanceof Page);
    }

    public function load() {

        $app = $this->page->getApp();
        if ($app) {
            $fName = $app . '.php';
            if (is_readable(APPS . '/' . $this->page->getControl() . "/" . $fName)) {
                require_once(APPS . '/' . $this->page->getControl() . "/" . $fName);
                $this->runApp($app);
            } else {
                throw new \Exception("No se puede leer la aplicación en " . APPS . '/' . $this->page->getControl() . "/" . $fName . ".");
            }
        } else {
            throw new \Exception("Error al iniciar app");
        }
        return $this;
    }

    private function runApp($appName) {
        $class = "apps\\" . $this->page->getControl() . "\\" . $appName;
        $_instance = new $class();
        $_instance->run();
    }

    public function runAjax($ajaxControl) {
        /* @var $_instance \core\ajax */
        $class = null;
        if ($ajaxControl === 'spkDebuggRunServer' && DEBUG) {
            $class = "core\\_assets\\php\\spkDebuggRunServer";
        } else {
            $class = "ajax\\" . $ajaxControl;
        }
        
        if (!class_exists($class)) {
            throw new \Exception('Class ' . $class . ' not found!');
        }
        $_instance = new $class();
        $_instance->run();

        return $_instance->getResult();
    }

    public function loadControl() {
        require_once(CONTROLS . $this->getPage()->getControl() . '.control.php');
    }

    /*
      public function showConsoleHtml() {
      return '<div style="color:#fff; padding:10px; text-align:center;background:#000;margin-top:50px;font-size:23px;">No se pasaron los par&aacutemetros esperados.</div><div style="font-variant: small-caps; text-align:right;">#iVirtualNetworks devCode v: ' . VERSION . '</div>';
      } */

    public function getParams($type, $key = false) {
        if ($key) {
            if (isset($this->urlParams[$type][$key])) {
                return $this->urlParams[$type][$key];
            }return false;
        } else {
            if (isset($this->urlParams[$type])) {
                return $this->urlParams[$type];
            }return false;
        }
    }
    
    /**
     * @param string $theme
     * @return Spk
     */
    public static function loadTheme($theme = "web") {
        self::$instance->page->loadTheme($theme);
        return self::$instance;
    }

    /**
     * @return \core\ViewParser
     */
    public static function getTpl($file, $filePath = "") {
        if ($filePath == "") {
            $filePath = VIEWS;
        }
        $tpl = new ViewParser($filePath . $file . ".html");
        $tpl->assignGlobal("webPath", WEB_PATH);
        $tpl->assignGlobal("version", APP_VERSION);
        return $tpl;
    }

    public static function addView(ViewParser $tpl, $item = "root") {
        self::$instance->views[$item] = $tpl;
    }

    public static function getViews() {
        return self::$instance->views;
    }

    /* public static function addError($error){
      array_push(self::$errors,$error);
      } */

    /* public static function getErrors() {
      if (!DEBUG) {
      return '';
      } else {
      require_once(ROOT_PATH . 'core/debugger.core.php');
      return \core\debugger::showErrors();
      }
      } */

    public static function pushDebugg($_info, $varName = '', $dType = 2) {
        if (DEBUG) {
            require_once(ROOT_PATH . 'core/debugger.core.php');
            $backtrace = debug_backtrace();
            \core\debugger::addToDebuggerOutput($_info, $varName, $dType, $backtrace[0]['file'], $backtrace[0]['line']);
        }
    }

    public static function getDebuggInfo() {
        if (!DEBUG) {
            return '';
        } else {
            require_once(ROOT_PATH . 'core/debugger.core.php');
            //return \core\debugger::getDebuggerOutput();
            return \core\debugger::getDebugger();
        }
    }
    
}
