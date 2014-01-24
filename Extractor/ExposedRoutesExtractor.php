<?php

/*
 * This file is part of the FOSJsRoutingBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\JsRoutingBundle\Extractor;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author      William DURAND <william.durand1@gmail.com>
 */
class ExposedRoutesExtractor implements ExposedRoutesExtractorInterface
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * Base cache directory
     *
     * @var string
     */
    protected $cacheDir;

    /**
     * @var array
     */
    protected $bundles;

    /**
     * @var array
     */
    protected $routesToExpose;

    /**
     * Default constructor.
     *
     * @param RouterInterface $router         The router.
     * @param array           $routesToExpose Some route names to expose.
     * @param string          $cacheDir
     * @param array           $bundles        list of loaded bundles to check when generating the prefix
     */
    public function __construct(RouterInterface $router, array $routesToExpose = array(), $cacheDir, $bundles = array())
    {
        $this->router         = $router;
        $this->routesToExpose = $routesToExpose;
        $this->cacheDir       = $cacheDir;
        $this->bundles        = $bundles;
    }

    /**
     * {@inheritDoc}
     */
    public function getRoutes()
    {
        $collection = $this->router->getRouteCollection();
        $routes     = new RouteCollection();

        /** @var Route $route */
        foreach ($collection->all() as $name => $route) {
            if ($this->isRouteExposed($route, $name)) {
                $routes->add($name, $route);
            }
        }

        return $routes;
    }

    /**
     * {@inheritDoc}
     */
    public function getCachePath($locale)
    {
        $cachePath = $this->cacheDir . DIRECTORY_SEPARATOR . 'fosJsRouting';
        if (!file_exists($cachePath)) {
            mkdir($cachePath);
        }

        if (isset($this->bundles['JMSI18nRoutingBundle'])) {
            $cachePath = $cachePath . DIRECTORY_SEPARATOR . 'data.' . $locale . '.json';
        } else {
            $cachePath = $cachePath . DIRECTORY_SEPARATOR . 'data.json';
        }

        return $cachePath;
    }

    /**
     * {@inheritDoc}
     */
    public function getResources()
    {
        return $this->router->getRouteCollection()->getResources();
    }

    /**
     * {@inheritDoc}
     */
    public function isRouteExposed(Route $route, $name)
    {
        $pattern = $this->buildPattern();

        return true === $route->getOption('expose')
            || 'true' === $route->getOption('expose')
            || ('' !== $pattern && preg_match('#' . $pattern . '#', $name));
    }

    /**
     * Convert the routesToExpose array in a regular expression pattern
     *
     * @return string
     */
    protected function buildPattern()
    {
        $patterns = array();
        foreach ($this->routesToExpose as $toExpose) {
            $patterns[] = '(' . $toExpose . ')';
        }

        return implode($patterns, '|');
    }
}
