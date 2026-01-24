<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        '/api/admin/genslug',
        '/api/find-next-post',
        '/api/admin/change-active',
        '/api/admin/getUserInfo',
        '/api/admin/create-row',
        '/api/admin/remove-row',
        '/api/admin/update-row-name',
        '/api/admin/update-row-color',
    ];
}
