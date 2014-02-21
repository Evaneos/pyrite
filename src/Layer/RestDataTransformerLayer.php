<?php

namespace Pyrite\Layer;

use Pyrite\Response\ResponseBag;
use Pyrite\Layer\AbstractLayer;
use Pyrite\Layer\Layer;

class RestDataTransformerLayer extends AbstractLayer implements Layer
{

    public function acceptsByForcedTypeOrHttpAccept(array $acceptTypes = array())
    {
        if (!empty($this->config['type'])) {
            return $this->config['type'];
        }

        $acceptedType = $this->acceptsByHttpAccept($acceptTypes);

        if ($acceptedType !== false) {
            return $acceptedType;
        }

        return $acceptTypes[0];
    }

    private function acceptsByHttpAccept(array $acceptTypes)
    {
        $acceptableContentTypes = $this->request->getAcceptableContentTypes();
        foreach ($acceptableContentTypes as $acceptableContentType) {
            if (in_array($acceptableContentType, $acceptTypes)) {
                return $acceptableContentType;
            }
        }
        return false;
    }



    public function after(ResponseBag $bag)
    {
        $this->bag = $bag;
        $actionResult = $bag->get(ResponseBag::ACTION_RESULT, false);

        $hasResult = false !== $actionResult;

        if (!$hasResult) {
            return;
        }

        $type = $this->acceptsByForcedTypeOrHttpAccept(array('application/json','application/xml','text/html'));
        $data = $this->bag->get('data');

        switch ($type) {
            case 'application/xml':
                header('Content-type: application/xml; charset=UTF-8');
                $view = xmlrpc_encode($data);
                break;
            case 'text/html':
                header('Content-type: text/html; charset=UTF-8');
                $view = '<pre>'.print_r($data,true).'</pre>';
                break;
            default:
                header('Content-type: application/json; charset=UTF-8');
                $view = json_encode($data);
                break;
        }

        $bag->set('view', $view);
    }

}