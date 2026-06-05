<?php

namespace App\Routes;

use Controllers\ProfileController;
use Middleware\AuthMiddleware;

class ProfileRoutes
{
    private $app;
    private $profileController;

    public function __construct($app, ProfileController $profileController)
    {
        $this->app = $app;
        $this->profileController = $profileController;
    }

    /**
     * Register all profile routes
     */
    public function register()
    {
        // Public routes
        $this->app->post('/profiles/login', [$this->profileController, 'loginProfile']);

        // Protected routes
        $this->app->get('/profiles', [$this->profileController, 'adminGettingAllUsers']);
        $this->app->add(new AuthMiddleware());

        $this->app->get('/profiles/{employee_id}', [$this->profileController, 'getProfileById']);
        $this->app->add(new AuthMiddleware());

        $this->app->post('/profiles', [$this->profileController, 'adminCreatingUser']);
        $this->app->add(new AuthMiddleware());

        $this->app->patch('/profiles/{employee_id}', [$this->profileController, 'adminUpdatingUser']);
        $this->app->add(new AuthMiddleware());

        $this->app->delete('/profiles/{employee_id}', [$this->profileController, 'adminDeletingUser']);
        $this->app->add(new AuthMiddleware());

        $this->app->patch('/profiles/{employee_id}/update-password', [$this->profileController, 'updatePassword']);
        $this->app->add(new AuthMiddleware());

        $this->app->patch('/profiles/{employee_id}/reset-password', [$this->profileController, 'resetPassword']);
        $this->app->add(new AuthMiddleware());

        $this->app->post('/profiles/clear-cache', [$this->profileController, 'clearCache']);
        $this->app->add(new AuthMiddleware());
    }
}