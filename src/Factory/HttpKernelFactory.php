<?php

namespace Pyrite\Factory;

use Symfony\Component\HttpKernel\HttpKernelInterface;

interface HttpKernelFactory
{
    /**
     * Create an HttpKernelInterface
     *
     * @param HttpKernelInterface $app         The wrapped kernel
     * @param string              $routeName   Name of the route used
     * @param array               $parameters  Parameters for this route
     *
     * @return <string, HttpKernelInterface> Return name a kernel and the kernel
     */
    public function register(HttpKernelInterface $app = null, $name = '', array $parameters = array());
}
