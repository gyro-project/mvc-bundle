<?php

namespace Gyro\Bundle\MVCBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Turbolinks makes navigating your web application faster.
 *
 * When you follow a link, Turbolinks automatically fetches the page, swaps in
 * its <body>, and merges its <head>, all without incurring the cost of a full
 * page load.
 *
 * This Listener handles the server side of Turbolinks by setting the
 * `Turbolinks-Location' header correctly.
 *
 * @link https://github.com/turbolinks/turbolinks
 */
class TurbolinksListener
{
    /**
     * @param ResponseEvent|GetResponseEvent $event
     */
    public function onKernelResponse($event) : void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        $session = $request->getSession();

        if (!$request->isXmlHttpRequest() || !$session) {
            return;
        }

        if ($response->isRedirect() && $request->headers->has('Turbolinks-Referrer')) {
            $session->set('turbolinks_location', $response->headers->get('Location'));
        } elseif ($session->has('turbolinks_location')) {
            $response->headers->set('Turbolinks-Location', $session->get('turbolinks_location'));
            $session->remove('turbolinks_location');
        }
    }
}
