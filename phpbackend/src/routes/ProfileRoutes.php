<?php

namespace App\Routes;

use App\Controllers\ProfileController;
use App\Middleware\AuthMiddleware;

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
        $this->app->get('/profiles', [$this->profileController, 'getProfiles']);
        $this->app->add(new AuthMiddleware());

        $this->app->get('/profiles/{employee_id}', [$this->profileController, 'getProfileById']);
        $this->app->add(new AuthMiddleware());

        $this->app->post('/profiles', [$this->profileController, 'createProfile']);
        $this->app->add(new AuthMiddleware());

        $this->app->patch('/profiles/{employee_id}', [$this->profileController, 'updateProfile']);
        $this->app->add(new AuthMiddleware());

        $this->app->delete('/profiles/{employee_id}', [$this->profileController, 'deleteProfile']);
        $this->app->add(new AuthMiddleware());

        $this->app->patch('/profiles/{employee_id}/update-password', [$this->profileController, 'updatePassword']);
        $this->app->add(new AuthMiddleware());

        $this->app->patch('/profiles/{employee_id}/reset-password', [$this->profileController, 'resetPassword']);
        $this->app->add(new AuthMiddleware());

        $this->app->post('/profiles/clear-cache', [$this->profileController, 'clearCache']);
        $this->app->add(new AuthMiddleware());
    }
}