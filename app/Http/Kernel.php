<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \App\Http\Middleware\TrustProxies::class,
        \Fruitcake\Cors\HandleCors::class,
        \App\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        //more
        \App\Http\Middleware\EnableCrossRequestMiddleware::class
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:60,1',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Session\Middleware\StartSession::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    //More
        //User
        'login.check' => \App\Http\Middleware\User\LoginCheck::class,
        'user.exist.check' => \App\Http\Middleware\User\ExistCheck::class,
        'owner.check' => \App\Http\Middleware\User\OwnerCheck::class,
        //Manager
        'manager.login.check' => \App\Http\Middleware\Manager\LoginCheck::class,
        'manager.super.check' => \App\Http\Middleware\Manager\SuperPowerCheck::class,
        //Upick
        'food.exist.check' => \App\Http\Middleware\Food\ExistCheck::class,
        //EatestComment
        'comment.from.check' => \App\Http\Middleware\Eatest\Comments\FromCheck::class,
        'comment.exist.check' => \App\Http\Middleware\Eatest\Comments\ExistCheck::class,
        'comment.owner.check' => \App\Http\Middleware\Eatest\Comments\OwnerCheck::class,
        //EatestReply
        'reply.exist.check' => \App\Http\Middleware\Eatest\Reply\ExistCheck::class,
        'reply.tofrom.check' => \App\Http\Middleware\Eatest\Reply\FromToCheck::class,
        'reply.owner.check' => \App\Http\Middleware\Eatest\Reply\OwnerCheck::class,
        //Eatest
        'owner.eatest.check' => \App\Http\Middleware\Eatest\OwnerCheck::class,
        'eatest.exist.check' => \App\Http\Middleware\Eatest\ExistCheck::class,
        //Course
        'owner.course.check' => \App\Http\Middleware\Course\OwnerCheck::class,
        'course.exist.check' => \App\Http\Middleware\Course\ExistCheck::class,
        //CountDown
        'countdown.exist.check' => \App\Http\Middleware\jwxt\CountDownExistCheck::class,
        'owner.countdown.check' => \App\Http\Middleware\jwxt\OwnerCountDownCheck::class,
        //FocusOn
        'focus.exist.check' => \App\Http\Middleware\Focus\FocusExistCheck::class,
        'unfocus.exist.check' => \App\Http\Middleware\Focus\UnFocusExistCheck::class,
    ];
}
