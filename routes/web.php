<?php

Route::group(['domain' => 'sky.' . env('APP_DOMAIN'), 'namespace' => studly_case('sky')], function () {
    Route::post('/mailgun', 'MailgunController');

    Route::middleware('guest')->group(function () {
        Route::name('sky')->get('/', 'Auth\LoginController@showLoginForm');
        Route::post('/', 'Auth\LoginController@login');

        Route::name('sky.password.reset')->get('/password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm');
        Route::name('sky.password.email')->post('/password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');

        Route::name('sky.password.reset.token')->get('/password/reset/{token}', 'Auth\ResetPasswordController@showResetForm');
        Route::post('/password/reset', 'Auth\ResetPasswordController@reset');

        Route::name('sky.register')->get('/register', 'Auth\RegisterController@showRegistrationForm');
        // Route::post('/register', 'Auth\RegisterController@register');
    });

    Route::middleware(['auth', 'domain:sky'])->group(function () {
        Route::get('/import/clients', 'ImportController@clients');

        Route::name('sky.api.load-data')->get('/api/load-data', 'ApiController@loadData');
        Route::name('sky.api.change-project')->post('/api/change-project/{project?}', 'ApiController@changeProject');
        Route::name('sky.api.change-report-view')->post('/api/change-report-view/{view?}', 'ApiController@changeReportView');
        Route::name('sky.logout')->post('/logout', 'Auth\LoginController@logout');

        Route::middleware('permission:Download')->group(function () {
            Route::name('sky.api.download')->get('/api/download/{path}', 'ApiController@download');
        });

        Route::middleware('permission:Change Status')->group(function () {
            Route::name('sky.api.status')->get('/api/status/{path}', 'ApiController@status');
        });

        Route::middleware('permission:Complete Task')->group(function () {
            Route::name('sky.api.complete-task')->get('/api/complete-task/{path}', 'ApiController@completeTask');
        });

        Route::middleware('permission:Upload')->group(function () {
            Route::name('sky.api.upload')->post('/api/upload/{path}', 'ApiController@upload');
        });

        Route::middleware('permission:Reorder')->group(function () {
            Route::name('sky.api.order')->post('/api/order/{path}', 'ApiController@order');
        });

        Route::middleware('permission:Move')->group(function () {
            Route::name('sky.api.move')->get('/api/move/{path}', 'ApiController@move');
            Route::name('sky.api.move-confirm')->post('/api/move/{path}', 'ApiController@moveConfirm');
        });

        Route::middleware('permission:Reply to messages')->group(function () {
            Route::name('sky.api.reply')->match(['post', 'put', 'patch'], '/api/reply/{path}', 'ApiController@reply');
        });

        Route::middleware('permission:Delete Photo')->group(function () {
            Route::name('sky.api.delete-image')->post('/api/delete-image/{path}', 'ApiController@deleteImage');
        });

        Route::middleware('permission:Test Newsletter')->group(function () {
            Route::name('sky.api.test-newsletter')->get('/api/test-newsletter/{path}', 'ApiController@testNewsletter');
        });

        Route::middleware('permission:Send Newsletter')->group(function () {
            Route::name('sky.api.send-newsletter')->get('/api/send-newsletter/{path}', 'ApiController@sendNewsletter');
        });

        Route::middleware('permission:Test SMS')->group(function () {
            Route::name('sky.api.test-sms')->get('/api/test-sms/{path}', 'ApiController@testSms');
        });

        Route::middleware('permission:Send SMS')->group(function () {
            Route::name('sky.api.send-sms')->get('/api/send-sms/{path}', 'ApiController@sendSms');
        });

        Route::middleware('permission:Edit')->group(function () {
            Route::name('sky.api.edit')->get('/api/edit/{path}', 'ApiController@edit');
            Route::name('sky.api.update')->match(['put', 'patch'], '/api/{path}', 'ApiController@update');
        });

        Route::middleware('permission:Delete')->group(function () {
            Route::name('sky.api.delete')->get('/api/delete/{path}', 'ApiController@delete');
            Route::name('sky.api.destroy')->delete('/api/{path}', 'ApiController@destroy');
        });

        Route::middleware('permission:View: Dashboard')->group(function () {
            Route::name('sky.dashboard')->get('/dashboard', 'ReportController'); // DashboardController
        });

        Route::middleware('permission:View: Projects')->group(function () {
            Route::get('/projects', 'ApiController');
        });

        Route::middleware('permission:View: Apartments')->group(function () {
            Route::get('/apartments', 'ApiController');
        });

        Route::middleware('permission:View: Viewings')->group(function () {
            Route::get('/viewings', 'ApiController');
        });

        Route::middleware('permission:View: Sales')->group(function () {
            Route::get('/sales', 'ApiController');
        });

        Route::middleware('permission:View: Users')->group(function () {
            Route::middleware('permission:View: Admins')->group(function () {
                Route::get('/users', 'ApiController');
            });

            Route::middleware('permission:View: Agents')->group(function () {
                Route::get('/agents', 'ApiController');
            });

            Route::middleware('permission:View: Clients')->group(function () {
                Route::get('/clients', 'ApiController');
            });

            Route::middleware('permission:View: Investors')->group(function () {
                Route::get('/investors', 'ApiController');
            });
        });

        Route::middleware('permission:View: Office')->group(function () {
            Route::middleware('permission:View: Bookings')->group(function () {
                Route::get('/bookings', 'ApiController');
            });

            Route::middleware('permission:View: Contacts')->group(function () {
                Route::get('/contacts', 'ApiController');
            });

            Route::middleware('permission:View: Guests')->group(function () {
                Route::get('/guests', 'ApiController');
            });

            Route::middleware('permission:View: Newsletters')->group(function () {
                Route::get('/newsletters', 'ApiController');
            });

            Route::middleware('permission:View: SMS')->group(function () {
                Route::get('/sms', 'ApiController');
            });

            Route::middleware('permission:View: Tasks')->group(function () {
                Route::get('/tasks', 'ApiController');
            });

            Route::middleware('permission:View: Websites')->group(function () {
                Route::get('/websites', 'ApiController');

                Route::middleware('permission:View: Analytics')->group(function () {
                    Route::name('sky.analytics')->get('/websites/{id}', 'AnalyticsController');
                });
            });

            Route::middleware('permission:View: Analytics')->group(function () {
                Route::name('sky.view-google-analytics')->get('/view-google-analytics', 'ReportController@viewGoogleAnalytics');
                Route::name('sky.view-google-ads')->get('/view-google-ads', 'ReportController@viewGoogleAds');
                Route::name('sky.view-google-search-console')->get('/view-google-search-console', 'ReportController@viewGoogleSearchConsole');
                Route::name('sky.view-youtube')->get('/view-youtube', 'ReportController@viewYoutube');
            });
        });

        Route::prefix('reports')->middleware('permission:View: Reports')->group(function () {
            Route::post('/reports', 'ReportController');
            Route::name('sky.generate-report')->post('/generate-report/{slug}', 'ReportController@generate');
            Route::name('sky.export-report')->post('/export-report/{slug}', 'ReportController@export');
            Route::name('sky.report-value')->get('/report-value', 'ReportController@value');
            Route::name('sky.download-report')->get('/download-report/{slug}/{uuid?}', 'ReportController@download');

            Route::middleware('permission:View: Reports Dashboard')->group(function () {
                Route::get('/dashboard/{slug}', 'ReportController')->where('slug', 'sales|funding|analytics');
                Route::get('/{slug?}', 'ReportController')->where('slug', 'dashboard');
            });

            Route::middleware('permission:View: Agent Commissions Report')->group(function () {
                Route::get('/agent-commissions', 'ReportController');
            });

            Route::middleware('permission:View: Apartments Report')->group(function () {
                Route::get('/apartments', 'ReportController');
            });

            Route::middleware('permission:View: Cancellations Report')->group(function () {
                Route::get('/cancellations', 'ReportController');
            });

            Route::middleware('permission:View: Clients Report')->group(function () {
                Route::get('/clients', 'ReportController');
            });

            Route::middleware('permission:View: Closing Dates Report')->group(function () {
                Route::get('/closing-dates', 'ReportController');
            });

            Route::middleware('permission:View: Conversion Rate Report')->group(function () {
                Route::get('/conversion-rate', 'ReportController');
            });

            Route::middleware('permission:View: Discount Report')->group(function () {
                Route::get('/discount', 'ReportController');
            });

            Route::middleware('permission:View: Investors Report')->group(function () {
                Route::get('/investors', 'ReportController');
            });

            Route::middleware('permission:View: Sub-Agent Commissions Report')->group(function () {
                Route::get('/subagent-commissions', 'ReportController');
            });

            Route::middleware('permission:View: Sales Report')->group(function () {
                Route::get('/sales', 'ReportController');
            });

            Route::middleware('permission:View: Targets Report')->group(function () {
                Route::get('/targets', 'ReportController');
            });

            Route::middleware('permission:View: Tasks Report')->group(function () {
                Route::get('/tasks', 'ReportController');
            });

            Route::middleware('permission:View: Viewings Report')->group(function () {
                Route::get('/viewings', 'ReportController');
            });
        });

        Route::middleware('permission:View: Settings')->group(function () {
            Route::middleware('permission:View: Activities')->group(function () {
                Route::get('/activities', 'ApiController');
            });

            Route::middleware('permission:View: Categories')->group(function () {
                Route::get('/categories', 'ApiController');
            });

            Route::middleware('permission:View: Countries')->group(function () {
                Route::get('/countries', 'ApiController');
            });

            Route::middleware('permission:View: Departments')->group(function () {
                Route::get('/departments', 'ApiController');
            });

            Route::middleware('permission:View: Domains')->group(function () {
                Route::get('/domains', 'ApiController');
            });

            Route::middleware('permission:View: Fund Size')->group(function () {
                Route::get('/fund-size', 'ApiController');
            });

            Route::middleware('permission:View: Investment Range')->group(function () {
                Route::get('/investment-range', 'ApiController');
            });

            Route::middleware('permission:View: Payment Methods')->group(function () {
                Route::get('/payment-methods', 'ApiController');
            });

            Route::middleware('permission:View: Permissions')->group(function () {
                Route::get('/permissions', 'ApiController');
            });

            Route::middleware('permission:View: Project Features')->group(function () {
                Route::get('/features', 'ApiController');
            });

            Route::middleware('permission:View: Roles')->group(function () {
                Route::get('/roles', 'ApiController');
            });

            Route::middleware('permission:View: Sources')->group(function () {
                Route::get('/sources', 'ApiController');
            });

            Route::middleware('permission:View: Statuses')->group(function () {
                Route::get('/statuses', 'ApiController');
            });

            Route::middleware('permission:View: Tags')->group(function () {
                Route::get('/tags', 'ApiController');
            });
        });

        // Route::name('sky.files')->get('/files/{slug?}', 'FileController');

        Route::middleware('permission:Create')->group(function () {
            Route::name('sky.api.create')->get('/api/create/{path}', 'ApiController@create');
            Route::name('sky.api.store')->post('/api/{path}', 'ApiController@store');
        });

        Route::get('/{path}', 'ApiController');
    });
});
