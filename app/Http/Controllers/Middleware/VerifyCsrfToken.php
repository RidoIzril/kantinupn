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
        // ISI DENGAN ROUTE WEBHOOK MU PERSIS SESUAI route:list !!!
        'customer/xendit/webhook',
        'api/xendit/webhook',
        'xendit/webhook',
        // wildcard supaya semua lolos:
        'customer/xendit/*',
        'api/xendit/*',
        'xendit/*',
        // atau jika di subdomain/tes lokal:
        '*xendit*',
    ];
}