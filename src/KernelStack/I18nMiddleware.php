<?php

namespace Pyrite\KernelStack;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

class I18nMiddleware implements HttpKernelInterface, TerminableInterface
{
    /**
     * @var HttpKernelInterface
     */
    protected $app;

    /**
     * @var ParameterBag
     */
    protected $config;

    /**
     * I18nMiddleware constructor.
     *
     * @param HttpKernelInterface $app
     * @param ParameterBag        $config
     */
    public function __construct(HttpKernelInterface $app, ParameterBag $config)
    {
        $this->app = $app;
        $this->config = $config;
    }

    /**
     * @param Request $request
     * @param int     $type
     * @param bool    $catch
     *
     * @return Response
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        $host  = $request->getHttpHost();
        $requestedLocal = str_replace('.', '_', $host);
        $requestedLanguageId = str_replace('.', '_', $host);

        $locales = $this->config->get('default_locale');
        $languageIds = $this->config->get('languages');

        $locale = isset($locales[$requestedLocal])
            ? $locales[$requestedLocal]
            : $defaultLocale = $locales['default']
        ;

        $languageId = isset($languageIds[$requestedLanguageId])
            ? $languageIds[$requestedLanguageId]
            : $defaultLanguage = $languageIds['default']
        ;

        $this->config->set('current_locale', $locale);
        $this->config->set('language_id', $languageId);
        $this->config->set('default_language_id', $languageIds['default']);
        $this->config->set('default_locale', $locales['default']);
        $this->config->set('available_locales', $locales['all']);

        return $this->app->handle($request, $type, $catch);
    }

    /**
     * @param Request  $request
     * @param Response $response
     */
    public function terminate(Request $request, Response $response)
    {
        if($this->app instanceof TerminableInterface){
            $this->app->terminate($request, $response);
        }
    }
}
