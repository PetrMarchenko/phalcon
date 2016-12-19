<?php

namespace Shark\Library;

use Phalcon\Annotations\Annotation;

class AnnotationRouter extends \Phalcon\Mvc\Router\Annotations
{
    protected $_routePostfix = '';

    protected $_handler;

    /**
     * Adds a route to the router without any HTTP constraint
     *
     *<code>
     * $router->add('/about', 'About::index');
     *</code>
     *
     * @param string $pattern
     * @param string/array $paths
     * @param string $httpMethods
     * @return \Phalcon\Mvc\Router\Route
     */
    public function add($pattern, $paths = null, $httpMethods = null)
    {
        if ($this->_routePostfix) {
            $pattern = $pattern . $this->_routePostfix;
        }

        return parent::add($pattern, $paths, $httpMethods);
    }

    public function processControllerAnnotation($handler, Annotation $annotation)
    {
        if ($annotation->getName() == 'RoutePostfix') {
            $this->_handler = $handler;
            $args = $annotation->getArguments();
            $this->_routePostfix = '(' . join('|', $args) . ')';
        } elseif ($this->_handler != $handler) {
            $this->_routePostfix = '';
        }
        return parent::processControllerAnnotation($handler, $annotation);
    }
}
