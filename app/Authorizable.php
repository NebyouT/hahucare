<?php

namespace App;

use Illuminate\Support\Arr;

trait Authorizable
{
    /**
     * List of default method names of the Controllers and the related permission.
     */
    private $abilities = [
        'index' => 'view',
        'index_data' => 'view',
        'index_list' => 'view',
        'edit' => 'edit',
        'show' => 'view',
        'update' => 'edit',
        'create' => 'add',
        'store' => 'add',
        'destroy' => 'delete',
        'restore' => 'restore',
        'trashed' => 'restore',
    ];

    /**
     * Override of callAction to perform the authorization before.
     *
     * @return mixed
     */
    public function callAction($method, $parameters)
    {
        // Skip authorization for API requests — they are already protected
        // by the ApiPermissionMiddleware (api.permission) in the route definition.
        if (!$this->isApiRequest()) {
            if ($ability = $this->getAbility($method)) {
                $this->authorize($ability);
            }
        }

        return parent::callAction($method, $parameters);
    }

    public function getAbility($method)
    {
        $route = \Request::route();
        $routeName = $route ? $route->getName() : null;

        // If route has no name or name doesn't contain a dot, skip
        if (!$routeName || !str_contains($routeName, '.')) {
            return null;
        }

        $routeParts = explode('.', $routeName);
        $action = Arr::get($this->getAbilities(), $method);

        return ($action && isset($routeParts[1])) ? $action.'_'.$routeParts[1] : null;
    }

    /**
     * Check if the current request is an API request.
     */
    private function isApiRequest(): bool
    {
        $request = request();
        return $request->is('api/*') || $request->expectsJson() || $request->bearerToken();
    }

    private function getAbilities()
    {
        return $this->abilities;
    }

    public function setAbilities($abilities)
    {
        $this->abilities = $abilities;
    }
}
