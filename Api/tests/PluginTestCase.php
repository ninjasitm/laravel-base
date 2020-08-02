<?php

namespace Nitm\Api\Tests;

use Route;
use PluginTestCase as BasePluginTestCase;
use Cms\Classes\Theme;
use Cms\Classes\Page;
use Cms\Classes\Layout;
use Cms\Classes\CodeParser;
use Cms\Classes\ComponentManager;

abstract class PluginTestCase extends BasePluginTestCase
{
    protected $baseUrl;

    public function setUp()
    {
        parent::setUp();

        if (\Config::getEnvironment() == 'dev') {
            $this->baseUrl = 'https://nitm.thinklabserver.com/malcolm/api';
        } else {
            $this->baseUrl = 'https://api.dev.octopusartworks.com';
        }

        $this->setupRoutes();
      //   $this->component = ComponentManager::instance()->makeComponent('apiAPI', $this->spoofPageCode(), []);
    }

    protected function setupRoutes()
    {
        include __DIR__.'../../routes.php';
        Route::getRoutes()->refreshNameLookups();

      //   $longest = 0;
      //   foreach (Route::getRoutes() as $route) {
      //       $segments = floor(strlen($route->getUri()) / 6);
      //       if ($segments > $longest) {
      //           $longest = $segments;
      //       }
      //   }
      //   echo "Method\tRoute".str_repeat("\t", floor($longest))."Handler\n";
      //   foreach (Route::getRoutes() as $route) {
      //       $segments = ceil(strlen($route->getUri()) / 6);
      //       if ($segments >= $longest) {
      //           $tabRepeat = 1;
      //       } else {
      //           $tabRepeat = $segments < $longest ? ($longest - $segments) : $longest;
      //       }
      //       echo "\n".$route->getMethods()[0]."\t".$route->getUri().str_repeat("\t", floor($tabRepeat)).$route->getActionName();
      //   }
      //   foreach (Route::getRoutes() as $route) {
      //       if (is_string($route->getActionName())) {
      //           @list($controllerName, $action) = explode('@', $route->getActionName());
      //           if (in_array($controllerName, [
      //           'Nitm\Api\Controllers\ApiController',
      //             'Nitm\Api\Controllers\AuthController',
      //        ])) {
      //               $controller = \Mockery::mock($controllerName.'['.$action.']');
      //               echo "\nInstantiating $controllerName";
      //               \App::instance($controllerName, $controller);
      //           }
      //       }
      //   }
    }

    /**
     * Create a blank page for the component.
     *
     * @param array $routerParameters The parameters for the route
     * @param array $pageSettings     The settings for the generated page
     *
     * @return CodeBase The code base page object
     */
    protected function spoofPageCode()
    {
        $theme = Theme::load('test');
        $page = Page::load($theme, 'index.html') ?: new Page();
        $controllerClass = $this->controllerClass;
        $controller = new $controllerClass($theme);
        $parser = new CodeParser($page);
        $pageObj = $parser->source($page, 'no-layout', $controller);

        return $pageObj;
    }

    protected function setupDummyRoutes()
    {
        Route::get('test', array(function () {
        }));
    }
}
