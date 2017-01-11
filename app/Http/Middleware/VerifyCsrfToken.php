<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/uploader/image',
        '/marketing/black/upload',
        'marketing/group/upload',
        'mall/manager/upload',
        '/monthlyreport/assessment/import-template',
        '/assistant/employee/import'
    ];
}
