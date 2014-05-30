<?php

namespace Pyrite\OutputBuilder;

class XmlOutputBuilder implements OutputBuilder {

    public function getHeaders($data)
    {
        return array('Content-type: application/xml; charset=UTF-8');
    }

    public function buildOutput($data)
    {
        return xmlrpc_encode($data);
    }
}