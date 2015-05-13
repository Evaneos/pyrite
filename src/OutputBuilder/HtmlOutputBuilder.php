<?php

namespace Pyrite\OutputBuilder;

use Pyrite\Response\ResponseBag;
use VarDump\VarDump;
use VarDump\Formatters\HtmlFormatter;

class HtmlOutputBuilder implements OutputBuilder
{
    /**
     * @var HtmlFormatter
     */
    protected $formatter;

    /**
     * @var string
     */
    protected $backgroundColor;

    /**
     * @var string
     */
    protected $color;

    /**
     * @param HtmlFormatter
     */
    public function __construct(HtmlFormatter $formatter, $backgroundColor = '1A1A1A', $color = 'FCFCFC') {
        $this->formatter = $formatter;
        $this->backgroundColor = $backgroundColor;
        $this->color = $color;
    }

    public function getHeaders(ResponseBag $bag)
    {
        return array('Content-type: text/html; charset=UTF-8');
    }

    public function buildOutput(ResponseBag $bag)
    {
        $varDump = new VarDump($this->formatter, 99);
        return '
<html style="margin:0; padding:0;">
    <head>
        <meta charset="utf-8">
    </head>
    <body style="margin:0; padding:5px 10px; background:#' . $this->backgroundColor . '; color:#' . $this->color . '">
        ' . ($bag->has('resource') ? '<h1>Resource: ' . htmlspecialchars($bag->get('resource'), ENT_QUOTES, 'UTF-8') . '</h1>' : '') . '
        ' . ($bag->has('filters') ? '<h2>Filters:</h2>' . $varDump->dump($bag->get('filters')) : '') . '
        <h2>Data:</h2>
        ' . $varDump->dump($bag->get('data')) . '
    </body>
</html>';
    }
}
