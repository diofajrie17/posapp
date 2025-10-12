<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Arr;


class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
   public function share(Request $request): array
{
    return array_merge(parent::share($request), [
        'auth' => [
            'user' => $request->user()
                ? Arr::only($request->user()->toArray(), ['id','name','email'])
                : null,
            'roles' => $request->user()?->getRoleNames() ?? [],
            'permissions' => $request->user()?->getAllPermissions()->pluck('name') ?? [],
        ],
        'flash' => [
            'message' => fn () => $request->session()->get('message')
        ],
    ]);
}
}
