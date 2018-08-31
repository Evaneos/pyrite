<?php

namespace Pyrite\OutputBuilder;

use Pyrite\Response\ResponseBag;

class XmlOutputBuilder implements OutputBuilder
{
    public function buildOutput(ResponseBag $bag)
    {
        $bag->addHeader('Content-type', 'application/xml; charset=UTF-8');

        $bag->setResult(xmlrpc_encode($bag->get('data')));
    }
}
