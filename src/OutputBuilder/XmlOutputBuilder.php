<?php

namespace Pyrite\OutputBuilder;

use Pyrite\Response\ResponseBag;

class XmlOutputBuilder implements OutputBuilder
{

    public function getHeaders(ResponseBag $bag)
    {
        return array('Content-type: application/xml; charset=UTF-8');
    }

    public function buildOutput(ResponseBag $bag)
    {
        return xmlrpc_encode($bag->get('data'));
    }
}
