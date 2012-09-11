<?php
/**
 * Copyright (c) 2012 Soflomo http://soflomo.com.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the names of the copyright holders nor the names of the
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package     SlmLocale
 * @subpackage  Strategy
 * @author      Jurian Sluiman <jurian@soflomo.com>
 * @copyright   2012 Soflomo http://soflomo.com.
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://ensemble.github.com
 */

namespace SlmLocale\Strategy;

use SlmLocale\LocaleEvent;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class UriPathStrategy extends AbstractStrategy implements ServiceManagerAwareInterface
{
    const REDIRECT_STATUS_CODE = 302;

    protected $redirectWhenFound = true;

    protected $serviceManager;

    public function setOptions(array $options = array())
    {
        if (array_key_exists('redirect_when_found', $options)) {
            $this->redirectWhenFound = filter_var($options['redirect_when_found'], FILTER_VALIDATE_BOOLEAN);
        }
    }

    /**
     * Set service manager instance
     *
     * @param ServiceManager $locator
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    public function detect(LocaleEvent $event)
    {
         $request = $event->getRequest();
         if (!method_exists($request, 'getUri')) {
            return;
         }

         $locale = $this->detectLocaleInRequest($event->getRequest());

         if (!strlen($locale)) {
             return;
         }

         if (!$event->hasSupported() || !in_array($locale, $event->getSupported())) {
             return;
         }

         $router  = $this->serviceManager->get('router');
         if (method_exists($router, 'getBaseUrl')) {
             $baseurl = $router->getBaseUrl();
             $router->setBaseUrl('/'. $locale);
         }

         return $locale;
    }

    public function found(LocaleEvent $event)
    {
        $request = $event->getRequest();
        if (!method_exists($request, 'getUri')) {
           return;
        }

        $uri     = $event->getRequest()->getUri();
        $locale  = $event->getLocale();

        if (null === $locale) {
           return;
        }

        $router  = $this->serviceManager->get('router');

        if (method_exists($router, 'getBaseUrl')) {
             $router->setBaseUrl('/'. $locale);
        }

         if ($locale == $this->detectLocaleInRequest($event->getRequest())) {
             if (substr($uri->getPath(), -1) != '/') {
                 $response = $event->getResponse();
                 $location = $uri->toString() . '/';

                $response->setStatusCode(self::REDIRECT_STATUS_CODE);
                $response->getHeaders()->addHeaderLine('Location', $location);

                $response->send();
             }

             return;
         }

         $uri->setPath('/' . $locale . $uri->getPath());

        // if ($baseurl !== null) {
             // $uri->setPath($uri->getPath() . ((substr($uri->getPath(), -1) == '/') ? '' : '/'));
        // }

        if (!$this->redirectWhenFound) {
            return;
        }

        $location = $uri->toString();

         $response = $event->getResponse();

        $response->setStatusCode(self::REDIRECT_STATUS_CODE);
        $response->getHeaders()->addHeaderLine('Location', $location);

        $response->send();
    }

    protected function detectLocaleInRequest($request)
    {
         $uri    = $request->getUri();
         $path   = $uri->getPath();
         $parts  = explode("/", trim($path, '/'));
         $locale = array_shift($parts);

         return $locale;

    }
}
