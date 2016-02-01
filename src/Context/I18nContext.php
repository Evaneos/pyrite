<?php

namespace Pyrite\Context;

class I18nContext
{
    /**
     * @var string
     */
    private $currentLocale;

    /**
     * @var array
     */
    private $availableLocales;

    /**
     * @var int
     */
    private $languageId;

    /**
     * I18nContext constructor.
     *
     * @param       $currentLocale
     * @param       $languageId
     * @param array $availableLocales
     */
    public function __construct($currentLocale, $languageId, array $availableLocales = array())
    {
        $this->currentLocale = $currentLocale;
        $this->languageId = $languageId;
        $this->availableLocales = $availableLocales;
    }

    /**
     * @return string
     */
    public function getCurrentLocale()
    {
        return $this->currentLocale;
    }

    /**
     * @return array
     */
    public function getAvailableLocales()
    {
        return $this->availableLocales;
    }

    /**
     * @return int
     */
    public function getLanguageId()
    {
        return $this->languageId;
    }
}
