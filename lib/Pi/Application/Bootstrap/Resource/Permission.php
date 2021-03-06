<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Application\Bootstrap\Resource;

use Pi;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\Mvc\MvcEvent;

/**
 * ACL bootstrap resource
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Permission extends AbstractResource
{
    /**
     * {@inheritDoc}
     */
    public function boot()
    {
        // Boot user resource
        $this->engine->bootResource('authentication');

        $events = $this->application->getEventManager();

        // Setup action cache strategy
        $sharedEvents = $events->getSharedManager();
        // Attach listeners to controller
        $sharedEvents->attach(
            'PI_CONTROLLER',
            MvcEvent::EVENT_DISPATCH,
            [$this, 'checkAction'],
            99999
        );
    }

    /**
     * Check access to module action
     *
     * @param MvcEvent $e
     *
     * @return void
     */
    public function checkAction(MvcEvent $e)
    {
        // Skip cache if error occurred
        if ($e->isError()) {
            return;
        }

        // Deny all access for close/maintenance
        if (!isset($this->options['check_close'])
            || false !== $this->options['check_close']
        ) {
            if (Pi::config('site_close') && !Pi::service('permission')->isAdmin()) {
                $message = __('The website is in maintenance - we are back in a couple of minutes !');
                $this->denyAccess($e, $message);
                return;
            }
        }

        // Grant permission for admin
        if (Pi::service('permission')->isAdmin()) {
            return;
        }

        $section    = $this->engine->section();
        $routeMatch = $e->getRouteMatch();
        $route      = [
            'section'    => $section,
            'module'     => $routeMatch->getParam('module'),
            'controller' => $routeMatch->getParam('controller'),
            'action'     => $routeMatch->getparam('action'),
        ];

        // Skip module access check for system front section and admin login
        if ('system' == $route['module']
            && (
                'front' == $section
                || in_array($route['controller'], ['login'])
            )
        ) {
            // Grant access permission to system home page and dashboard for all admins
        } elseif ('system' == $route['module']
            && in_array($route['controller'], ['index', 'dashboard'])
            && Pi::service('user')->hasIdentity()
        ) {
            // Check against module access
        } else {
            $moduleAccess = Pi::service('permission')->modulePermission($route['module']);
            if (!$moduleAccess) {
                $this->denyAccess($e);
            }
        }

        // Skip page access check
        if (empty($this->options['check_page'])) {
            return;
        }

        // Check controller exceptions for permission check
        $controller = $e->getTarget();
        if ($controller instanceof AbstractController
            && method_exists($controller, 'permissionException')
        ) {
            $exceptions = $controller->permissionException();
            if ($exceptions) {
                // Skip check against controller
                if (is_bool($exceptions) && true === $exceptions) {
                    return;
                }
                // Skip check against action
                if (in_array($route['action'], (array)$exceptions)) {
                    return;
                }
            }
        }

        // Check action permission check against route
        $actionAccess = Pi::service('permission')->pagePermission($route);

        // Set up deny process
        if (false === $actionAccess) {
            $this->denyAccess($e);
        }

        return;
    }

    /**
     * Check if current module access is allowed
     *
     * @param MvcEvent $e
     *
     * @return bool
     */
    public function checkModule(MvcEvent $e)
    {
        // Grant permission for admin
        if (Pi::service('permission')->isAdmin()) {
            return;
        }

        //d(__METHOD__);
        $module = $e->getRouteMatch()->getParam('module');
        $access = Pi::service('permission')->modulePermission($module);
        if (!$access) {
            $this->denyAccess($e);
        }

        return;
    }

    /**
     * Set denied error
     *
     * @param MvcEvent $e
     * @param          $message
     *
     * @return void
     */
    protected function denyAccess(MvcEvent $e, $message = true)
    {
        $statusCode = Pi::service('user')->getUser()->isGuest()
            ? 401 : 403;
        $e->getResponse()->setStatusCode($statusCode);
        $e->setError($message);
    }
}
