<?php
/**{v=0.5-a}**/
/****************************************************************************/
//
//      -------------------------------------------------------------------
//      |                         VParser 0.5-a "Sparky"                  |
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

class ViewParser {

    private $file = ''; //archivo con la vista.
    private $rootNode = null; //nodo raíz
    private $pointer = null;
    private $globals = array();
    public $globalOn = false;
    public $files = null;
    public $incFiles = false;
    public $literals = false;
    private static $v='0.5-a';
    /**
     * Constructor de la clase ViewParser
     * @param   string  $file   Ruta al archivo con el template
     * @throws  ViewParserException
     * @throws  ViewParserIOException
     */
    public function __construct($file, $literals = false, $incFiles = false) {
        try {
            if (!file_exists($file)) {
                throw new ViewParserIOException("No se encontró el archivo " . $file);
            }
            $this->file = $file;
            $this->incFiles = $incFiles;
            $this->literals = $literals;
            $this->files = ($incFiles) ? array() : null;
            $this->__runParser();
        } catch (Exception $ex) {
            throw new ViewParserException('Error genérico', 0, $ex);
        }
    }

    private function __runParser() {
        $vpLines = new ViewParseLines($this->file);
        $this->rootNode = new ViewParseNode($vpLines, 0, $vpLines->total, '_ROOT', $this);
        //$this->rootNode->parseLines();
        $this->pointer = $this->rootNode;
    }

    public function isGlobal($key) {
        return isset($this->globals[$key]);
        //return key_exists($key, $this->globals);
    }

    public function getGlobalValue($key) {
        return $this->globals[$key];
    }

    public function assign($key, $value) {
        $this->pointer->assign($key, $value);
    }

    public function assignGlobal($key, $value) {
        $this->globalOn = true;
        $this->globals["{" . $key . "}"] = $value;
    }

    public function newBlock($bName, $obj = null) {
        try {
            $this->pointer = $this->pointer->addNode($bName);
            if ($obj != null) {
                foreach ($obj as $key => $value) {
                    if (is_scalar($value))
                        $this->pointer->assign($key, $value);
                }
            }
        } catch (ViewParserBlockNotFoundException $ex) {
            if ($this->pointer->getParent() != null) {
                $this->pointer = $this->pointer->getParent()->addNode($bName);
                if ($obj != null) {
                    foreach ($obj as $key => $value) {
                        if (is_scalar($value))
                            $this->pointer->assign($key, $value);
                    }
                }
            } else {
                throw $ex;
            }
        }
    }

    public function getOutputContent() {
        return $this->rootNode->getOutput();
    }

    public function gotoBlock($bName) {
        if ($this->pointer->getLabel() == $bName) {
            return;
        } else if ($bName == '_ROOT') {
            $this->pointer = $this->rootNode;
            return;
        }
        $_node = $this->pointer;

        while ($_node->getParent() != null) {
            //$label = $_node->getParent()->getLabel();
            if ($_node->getParent()->getLabel() == $bName) {
                $this->pointer = $_node->getParent();
                return;
            }
            $_node = $_node->getParent();
        }
        return false;
    }

    public function printToScreen() {
        echo $this->rootNode->getOutput();
    }

    
    public static function getV(){
        return self::$v;
    }
}

class ViewParseLines {

    public $lines = array();
    public $total = 0;

    public function __construct($file) {
        $this->lines = file($file);
        $this->total = count($this->lines);
    }

}

class ViewParseVar {

    public $txt = '';
    public $varName = '';
    public $global = false;

    public function __construct($varName = '') {
        $this->varName = $varName;
    }

    public function __clone() {
        $this->txt = '';
    }

}

class ViewParseNode {

    private $label = '_ROOT';
    private $txt = array();
    private $vars = array();
    private $nodes = array();
    private $index = 0;
    private $content = array();
    private $parent = null;
    private $blocks = array();
    public $start = 0;
    public $offset = 0;
    public $tpl = null;

    public function __construct(ViewParseLines $vpLines, $start, $offset, $label, ViewParser $tpl, $parent = null) {
        $this->label = $label;
        $this->start = $start;
        $this->parent = $parent;
        $this->tpl = $tpl;
        $_literal = false;
        for ($i = $start; $i < $offset; $i++) {

            if ($this->tpl->incFiles) {
                if ($_literal) {
                    $_posLiteral = strpos($vpLines->lines[$i], "{/literal}");
                    if ($_posLiteral !== false) {
                        $_literal = false;
                        continue;
                    } else {
                        $this->txt[$this->index] = str_replace(array("{[", "]}"), array("{", "}"), $vpLines->lines[$i]);
                        $this->index++;
                        continue;
                    }
                }

                $_posLiteral = strpos($vpLines->lines[$i], "{literal}");
                if ($_posLiteral !== false) {
                    $_literal = true;
                    continue;
                }
            }

            preg_match('/{block name=(["\']{1})(?<name>[a-zA-Z0-9_]+)(["\']{1})}/', $vpLines->lines[$i], $matches);
            if (isset($matches['name'])) {
                //linea con bloque de codigo inicio
                $this->nodes[$this->index] = new self($vpLines, ($i + 1), $offset, $matches["name"], $this->tpl, $this);
                $i = $this->nodes[$this->index]->offset;
                $this->index++;
                continue;
            }
            preg_match('/{\/block}/', $vpLines->lines[$i], $matches);
            if (isset($matches[0])) {
                //linea con bloque de codigo de cierre
                $this->offset = $i;
                break;
            }

            if ($this->tpl->incFiles) {
                preg_match('/{include name=(["\']{1})(?<file>[a-zA-Z0-9_\.]+)(["\']{1})}/', $vpLines->lines[$i], $matches);
                if (isset($matches['file'])) {
                    if (!isset($this->tpl->files[$matches['file']])) {
                        $this->tpl->files[$matches['file']] = new ViewParseLines($matches['file']);
                    }
                    $_incNode = new self($this->tpl->files[$matches['file']], 0, $this->tpl->files[$matches['file']]->total, $matches['file'], $this->tpl, null);
                    if ($_incNode->index > 0) {
                        $_inctxt = $_incNode->getTxt();
                        $_incvars = $_incNode->getVars();
                        $_incnodes = $_incNode->getNodes();
                        if (!empty($_inctxt)) {
                            foreach ($_inctxt as $_i => $_t) {
                                $this->txt[$this->index + $_i] = $_t;
                            }
                        }
                        if (!empty($_incvars)) {
                            foreach ($_incvars as $_i => $_t) {
                                $this->vars[$this->index + $_i] = $_t;
                            }
                        }
                        if (!empty($_incnodes)) {
                            foreach ($_incnodes as $_i => $_t) {
                                $this->nodes[$this->index + $_i] = $_t;
                            }
                        }
                        $this->index+=$_incNode->index;
                    }
                    $_incNode = null;
                    continue;
                }
            }
            preg_match_all('/{([a-zA-Z0-9_]+)}/', $vpLines->lines[$i], $matches);
            if (isset($matches[0])) {
                if (empty($matches[0])) {
                    $this->txt[$this->index] = $vpLines->lines[$i];
                    $this->index++;
                    continue;
                }
                $line = $vpLines->lines[$i];
                $_pos = 0;
                $_arrMatches = array();
                foreach ($matches[0] as $m) {
                    $_pos = strpos($line, $m, $_pos);
                    if ($_pos !== false) {
                        $_arrMatches[$_pos] = $m;
                        $_pos+=strlen($m);
                    }
                }
                if (!empty($_arrMatches)) {
                    $_l = '';
                    $_p = 0;
                    foreach ($_arrMatches as $pos => $v) {
                        $_l = substr($line, $_p, ($pos - $_p));
                        if ($_l != "") {
                            $this->txt[$this->index] = $_l;
                            $this->index++;
                        }
                        $this->vars[$this->index] = new ViewParseVar($v);
                        $this->index++;
                        $_p = $pos + strlen($v);
                    }
                    $_l = substr($line, $_p);
                    if ($_l != "") {
                        $this->txt[$this->index] = $_l;
                        $this->index++;
                    }
                }
            }
        }
        $this->offset = $i;
        return;
    }

    public function __clone() {
        if (!empty($this->vars)) {
            $_v = array();
            foreach ($this->vars as $index => $var) {
                $_v[$index] = clone $var;
            }
            $this->vars = $_v;
        }
    }

    public function addNode($nodeName) {
        if ($this->label == $nodeName && $this->parent != null) {
            $_n = $this->parent->addNode($nodeName);
            return $_n;
        }
        if (!empty($this->nodes)) {
            foreach ($this->nodes as $index => $node) {
                if ($node->label === $nodeName) {
                    if (!isset($this->content[$index]))
                        $this->content[$index] = array();
                    $_n = clone($node);
                    $_n->parent = $this;
                    array_push($this->content[$index], $_n);
                    return $_n;
                }
            }
            throw new ViewParserBlockNotFoundException("blocknode not found!"); // prueba.
        }else {
            throw new ViewParserBlockNotFoundException("blocknode not found!");
        }
    }

    public function getParent() {
        return $this->parent;
    }

    public function getLabel() {
        return $this->label;
    }

    public function assign($key, $value) {
        $key = '{' . $key . '}';
        if (!empty($this->vars)) {
            foreach ($this->vars as $i => $var) {
                if ($var->varName === $key) {
                    $var->txt = $value;
                }
            }
        }
    }

    public function getOutput() {
        $output = '';
        if ($this->index > 0) {
            for ($i = 0; $i < $this->index; $i++) {
                if (isset($this->txt[$i])) {
                    $output .= $this->txt[$i];
                } else if (isset($this->vars[$i])) {
                    if ($this->tpl->globalOn && $this->tpl->isGlobal($this->vars[$i]->varName)) {
                        $output .= $this->tpl->getGlobalValue($this->vars[$i]->varName);
                    } else {
                        $output .= $this->vars[$i]->txt;
                    }
                    //$output .= $this->vars[$i]->txt;
                } else if (isset($this->content[$i])) {
                    if (!empty($this->content[$i])) {
                        foreach ($this->content[$i] as $k => $v) {
                            $output.=$v->getOutput();
                        }
                    }
                }
            }
        }
        return $output;
    }

    public function getVars() {
        return $this->vars;
    }

    public function getNodes() {
        return $this->nodes;
    }

    public function getTxt() {
        return $this->txt;
    }
    
}

class ViewParserException extends \Exception {
    
}

class ViewParserIOException extends ViewParserException {
    
}

class ViewParserBlockNotFoundException extends ViewParserException {
    
}
