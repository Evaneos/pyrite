<?php

namespace Pyrite\Layer;

use Pyrite\Response\ResponseBag;
use Pyrite\Layer\AbstractLayer;
use Pyrite\Layer\Layer;

class DebugViewLayer extends AbstractLayer implements Layer
{

    public function after(ResponseBag $bag)
    {
        $view = sprintf("<h1>DEBUG MODE</h1>\n<br />%s %s %s %s %s",
            $this->getRequest(),
            $this->getServer(),
            $this->getPost(),
            $this->getGet(),
            $this->getSession());


        $bag->set('view', $view);
    }


    protected function getRequest() {
        return $this->titleDecorator('request', $_REQUEST);
    }

    protected function getServer() {
        return $this->titleDecorator('server', $_SERVER);
    }

    protected function getPost() {
        return $this->titleDecorator('post', $_POST);
    }

    protected function getGet() {
        return $this->titleDecorator('get', $_GET);
    }

    protected function getSession() {
        return $this->titleDecorator('session', $_SESSION);
    }

    protected function dump($what) {
        ob_start();
        var_dump($what);
        return ob_get_clean();
    }

    protected function titleDecorator($name, $what) {
        return sprintf("<h2>Dumping %s DATA : </h2>\n<br />%s<hr />", $name, nl2br(str_replace(" ", str_repeat("&nbsp;", 4), $this->dump($what))));
    }
}