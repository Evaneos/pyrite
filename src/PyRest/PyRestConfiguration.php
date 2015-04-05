<?php

namespace Pyrite\PyRest;

use Symfony\Component\HttpFoundation\Request;
use Pyrite\PyRest\Configuration\Parser;

class PyRestConfiguration
{
    const CONFIG_KEY_VO = 'vo';
    const CONFIG_KEY_REST = 'rest';
    const CONFIG_KEY_TABLE = 'table';


    protected $request = null;
    protected $parsers = array();
    protected $parsed = array();

    public function getRequest()
    {
        return $this->request;
    }

    public function addParser(Parser $parser)
    {
        $this->parsers[] = $parser;
    }

    public function parseRequest(Request $request)
    {
        $this->request = $request;
        foreach ($this->parsers as $parser) {
            $this->parsed[$parser::NAME] = $parser->parse($request);
        }
    }

    public function getConfig($parserName, $default = null)
    {
        if (!array_key_exists($parserName, $this->parsed)) {
            return $default;
        }

        return $this->parsed[$parserName];
    }
}
