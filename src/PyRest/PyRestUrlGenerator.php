<?php

namespace Pyrite\PyRest;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PyRestUrlGenerator
{
    protected $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function getItem($resource, $id, $referenceType = UrlGeneratorInterface::ABSOLUTE_URL)
    {
        return $this->urlGenerator->generate('pyrest-get-one', array(
            'resource' => $resource,
            'id' => $id),
            $referenceType);
    }

    public function getCollection($resource, $referenceType = UrlGeneratorInterface::ABSOLUTE_URL)
    {
        return $this->urlGenerator->generate('pyrest-get-all', array('resource' => $resource), $referenceType);
    }

    public function getNested($parentName, $parentId, $embedName, $referenceType = UrlGeneratorInterface::ABSOLUTE_URL)
    {
        return $this->urlGenerator->generate('pyrest-sub-get-all', array(
                'filter_resource' => $parentName,
                'filter_id' => $parentId,
                'nested' => $embedName),
                $referenceType);
    }
}