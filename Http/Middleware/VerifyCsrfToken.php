<?php

namespace samarnas\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
          '/admin',
          '/adminEdit',
          '/prof_edit',
          '/manageUserview',
          '/user/delete',
          '/manageProviderview',
          '/provider/delete',
          '/country/create',
          '/countryView',
          '/countryEdit',
          '/country/update',
          '/country/destroy',
          '/gender/create',
          '/genderView',
          '/genderEdit',
          '/gender/edit',
          '/gender/delete',
          '/service/create',
          '/serviceView',
          '/EditService',
          '/service/edit',
          '/service/delete',
          '/category/create',
          '/categoryView',
          '/categoryEdit',
          '/category/edit',
          '/category/delete',
          '/getServicelist',
          '/priceView',
          '/priceEdit',
          '/priceDelete',
          '/price/update',
          '/price_delete',
          '/manage_document',
          '/customerView',
          '/customer/delete',
          '/providerView',
          '/customerId',
          // '/document/approve/{id}/{array}',
          '/cust_docReject',
          '/prov_docReject',
          '/prov_expdocReject',
          '/businessReject',
          '/priceEdited',
          '/custTransaction',
          '/custView'
          
    ];
}
