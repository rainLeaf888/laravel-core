<?php

Route::group(['prefix'=>'account'
                , 'namespace' => 'Modules\Account\Http\Controllers'
                , 'middleware'=>'login.auth'], function () {
                    Route::controllers([
                    '/'  => 'IndexController',
                    ]);
                });
