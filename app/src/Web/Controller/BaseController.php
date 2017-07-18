<?php

namespace Morrison\Web\Controller;

use Morrison\Core\Container;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseController
{
    /** @var \Symfony\Component\HttpFoundation\Response $response */
    protected $response = null;

    /** @var \Symfony\Component\HttpFoundation\Request $request */
    protected $request  = null;

    /** @var \Doctrine\DBAL\Connection $db */
    protected $db = null;

    /** @var \Doctrine\ORM\EntityManager $em */
    protected $em = null;

    /** @var Logger $logger */
    protected $logger = null;
    
    /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
    protected $session;
    
    public function __construct(Container $container)
    {
        $this->c = $this->container = $container;
        $this->request = $this->container['request'];
        $this->db = $this->container['db.morrison'];
        $this->em = $this->container['doctrine.entity_manager'];
        $this->logger = $this->container['log.main'];
        $this->session  = $this->container['session'];
    }


    public function setResponse($response)
    {
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Renders a view via twig.
     *
     * @param string   $view       The view name
     * @param array    $parameters An array of parameters to pass to the view
     * @param Response $response   A response instance
     *
     * @return Response A Response instance
     */
    public function render($view, array $parameters = array(), Response $response = null)
    {
        if (null === $response) {
            $response = new Response();
        }

        $response->setContent($this->container['twig']->render($view, $parameters));

        return $response;
    }
    
    /**
     * By design, flash messages are meant to be used exactly once
     * @param string $type message type
     * @param string $message
     */
    public function addFlash($type, $message)
    {
        $this->session->getFlashBag()->add($type, $message);
    }
    
    /**
     * Returns a RedirectResponse to the given URL.
     *
     * @param string $url    The URL to redirect to
     * @param int    $status The status code to use for the Response
     *
     * @return RedirectResponse
     */
    protected function redirect($url, $status = 302)
    {
        return new RedirectResponse($url, $status);
    }
}
