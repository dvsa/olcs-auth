<?php

/**
 * Authentication Module
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Auth;
use Zend\Mvc\MvcEvent;

/**
 * Authentication Module
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Module
{
    /**
     * Bootstrap the module
     *
     * @param MvcEvent $e
     */
    public function onBootstrap(MvcEvent $e)
    {
        $sm = $e->getApplication()->getServiceManager();
        $translator = $sm->get('translator');
        $translator->addTranslationFilePattern('phparray', __DIR__ . '/../config/language/', '%s.php');
    }

    /**
     * Get module config
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}
