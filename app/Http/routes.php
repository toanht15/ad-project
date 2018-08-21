<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return redirect('/advertiser/login');
});

//facebook callback url
Route::get('/fb/callback', 'LoginController@fbCallback')->name('fb_callback');
Route::get('/advertiser/login', 'LoginController@loginPage')->name('advertiser_login');

//Facebookアカウントとしてログインが必要
Route::group(['middleware' => 'auth'], function () {
    //広告アカウントの指定
    Route::post('/advertiser/login_ad_account', 'LoginController@setAdAccount')->name('login_ad_account');
//    Route::get('/advertiser/ad_account_page', 'AdAccountController@addAccountPage')->name('add_advertiser_page');
//    Route::post('/advertiser/add_account', 'AdAccountController@addAccount')->name('add_account');

    //instagram callback url
    Route::get('/ig_callback', 'AccountSettingController@callback');

    // select advertiser
    Route::get('/select_advertiser', 'LoginController@selectAdvertiser')->name('select_advertiser');

    //広告主としてログインが必要
    Route::group(['prefix' => 'advertiser', 'middleware' => 'auth:advertiser'], function () {
        Route::get('/', function () {
            return redirect()->route('advertiser_login');
        });

        //logout
        Route::get('/logout', 'LoginController@logout')->name('user_logout');

        // AccountSetting
        Route::get('/account_setting', 'AccountSettingController@view')->name('account_setting');

        //AccountSetting_instagram
        Route::get('/account_setting/auth', 'AccountSettingController@auth')->name('connect_instagram');
        Route::get('/account_setting/{id}/disconnect', 'AccountSettingController@disconnect')->name('remove_instagram');

//        //  AccountSetting_mediaAccount
//        Route::get('/account_setting', 'AccountSettingController@listPage')->name('media_account_list');
//
        Route::post('/account_setting/create', 'AccountSettingController@createMediaAccount')->name('create_media_account');
//        //  AccountSetting_mediaAccount_facebook
        Route::get('/account_setting/login_facebook', 'AccountSettingController@loginWithFacebook')->name('media_account_login_fb');
        Route::get('/account_setting/create', 'AccountSettingController@createFbMediaAccountPage')->name('create_media_account_page');
//        //  AccountSetting_mediaAccount_twitter
        Route::get('/account_setting/login_twitter', 'AccountSettingController@loginWithTwitter')->name('media_account_login_tw');
        Route::get('/account_setting/twitter_callback', 'AccountSettingController@createTwMediaAccountPage')->name('media_account_tw_callback');
//
//        // AccountSetting_EmailNotification
        Route::post('/account_setting/save_email', 'AccountSettingController@saveEmail')->name('save_email');
//        //  AccountSetting_Owned_CvPages
        Route::post('/account_setting/save_cv_pages', 'AccountSettingController@saveSiteCVPage')->name('save_owned_cv_pages');
//        //  AccountSetting_Owned_exclude_id_address
        Route::post('/account_setting/save_exclude_address', 'AccountSettingController@saveExcludeIPAddress')->name('save_owned_exclude_address');


        Route::get('/hashtag_setting', 'HashtagController@listPage')->name('hashtag_list');
        Route::delete('/hashtag_setting/delete/{id}', 'HashtagController@destroy')->name('remove_search_condition');
        Route::post('/hashtag_setting/save', 'HashtagController@createSearchCondition')->name('store_hashtag');

        //dashboard
        Route::get('/dashboard', 'DashboardController@dashboard')->name('dashboard');

        //images
        Route::get('/images/{hashtagId?}', 'ImageController@listPage')->name('image_list');
        Route::get('/images/edit/{targetId}', 'ImageController@editImage')->name('edit_image');
        Route::post('/images/upload', 'ImageController@upload')->name('upload_image');
        Route::post('/images/get_fb_image', 'ImageController@getImageFromFacebookLibrary')->name('get_facebook_image');
        Route::post('/images/delete_fb_image', 'ImageController@deleteFacebookImages')->name('delete_facebook_image');
        Route::get('/images/upload_video/{videoId}', 'ImageController@uploadVideo')->name('fb_upload_video');

        //slideshow
        Route::post('/slideshows/{slideshowId}/upload', 'SlideshowController@uploadToMediaAccount')->name('upload_slideshow');
        Route::get('/slideshows/{slideshowId}/kpi', 'SlideshowController@apiGetKpi')->name('get_slideshow_kpi');
        Route::get('/slideshows', 'SlideshowController@listPage')->name('slideshows');
        Route::get('/slideshows/{slideshowId}/delete', 'SlideshowController@delete')->name('delete_slideshow');
        Route::get('/slideshows/{slideshowId}/detail', 'SlideshowController@detail')->name('slideshow_detail');
        Route::get('/slideshows/delete_preview/{serialNo}', 'SlideshowController@delete_preview')->name('delete_preview');

        // offer detail
        Route::post('/offer/fb_upload', 'OfferController@uploadFbMaterial')->name('upload_material_fb');
        Route::post('/offer/tw_upload', 'OfferController@uploadTwMaterial')->name('upload_material_tw');
        Route::get('/offer/delete_img/{imageId?}', 'ImageController@deleteImage')->name('remove_edited_image');
        Route::get('/offer/get_edited_img_kpi/{imageId}', 'OfferController@apiGetEditedImgKpi')->name('get_edited_img_kpi');
        Route::post('/offer/archive', 'OfferController@archive')->name('archive_offer');

        // offer sets
        Route::post('/offers_set/create', 'OfferController@createOffers')->name('store_offerset');

        // post
        Route::post('/post/archive', 'ImageController@archive')->name('archive');
        Route::post('/post/un_archive', 'ImageController@unArchive')->name('un_archive');
        Route::get('/post/{id}', 'PostController@detail')->name('post_detail');

        // parts
        Route::get('/parts', 'PartController@listPage')->name('part_list');
        Route::get('/part/{id}', 'PartController@partDetail')->name('part_detail')->where('id', '[0-9]+');;

        Route::post('/part/{id}/delete', 'PartController@delete');

        // site
        Route::get('/sites', 'SiteController@listSite')->name('site_list');
        Route::post('/sites', 'SiteController@create');

        // item
        Route::get('/pages', 'ProductController@listPage')->name('page_list');
        Route::post('/vtdr_image/cancel_part/', 'PartController@deletePartImage')->name('cancel_part');

        // api routes
        Route::group(['prefix' => 'api'], function () {
            // dashboard
            Route::get('/dashboard/get_graph_data', 'DashboardController@apiGetGraphData')->name('dashboard_graph_api');
            Route::get('/dashboard/get_top_performance_ugc_api', 'DashboardController@apiGetTopPerformanceUgc')->name('get_top_performance_ugc_api');
            Route::get('/dashboard/get_ugc_status', 'DashboardController@apiGetUgcStatus')->name('get_ugc_status_api');
            Route::get('/dashboard/get_total_conversion', 'DashboardController@apiGetTotalConversion')->name('get_total_cv_api');

            // offer detail
            Route::post('/offer/save_edited', 'OfferController@saveEditedImage')->name('save_edited_image');
            Route::get('/offer/get_edited_img_list/{offerId}', 'OfferController@apiGetEditedImgList')->name('api_get_edited_image_list');

            // image list
            Route::post('/complete_tutorial', 'AdAccountController@completeTutorial')->name('api_complete_tutorial');
            Route::get('/images', 'ImageController@apiGetImageList')->name('api_get_images');
            Route::get('/statistic', 'ImageController@apiGetStatistic')->name('api_get_statistic');
            Route::get('/count_crawling_hashtag', 'ImageController@apiCountCrawlingHashtag')->name('api_count_crawling_hashtag');

            Route::get('/account_setting/list', 'AccountSettingController@apiGetList')->name('api_media_account_list');
            Route::post('/account_setting/change_advertiser_crawl_post_setting', 'AccountSettingController@apiChangeAdvertiserCrawlPostsSetting')->name('api_advertiser_crawl_post_setting');

            Route::post('/slideshows/create/{slideshowId?}', 'SlideshowController@create')->name('create_slideshow');
            Route::get('/get_slideshow_data/{slideshowId?}', 'SlideshowController@apiGetSlideshowData')->name('api_get_slideshow_data');

            Route::get('/get_searchcondition_statistic/{searchConditionId}', 'HashtagController@apiStatistic')->name('api_search_condition_statistic');

            //part

            Route::get('/parts', 'PartController@listPartApi')->name('api_part_list');
            Route::get('/parts/{id}', 'PartController@detail')->name('api_part_detail');
            Route::get('/parts/{id}/kpi', 'PartController@kpi')->name('part_kpi');
            Route::post('/parts/{id}/update_sort_value', 'PartController@updateSortValue')->name('api_part_update_sort_value');

            //page
            Route::get('/products', 'ProductController@apiGetAllProduct')->name('api_get_all_product');
            Route::get('/pages/{id}', 'ProductController@detail')->name('api_page_detail');
            Route::post('/pages/{id}', 'ProductController@update');
            Route::post('/images/{id}/delete', 'ProductController@deteleImage')->name('api_page_delete_image');
            Route::post('/image/{id}/products/', 'ProductController@apiSaveImageProduct')->name('api_save_image_product');
            Route::get('/page/get_cv_page', 'ProductController@apiGetCvPages')->name('api_get_cv_page');
            Route::post('/page/add_by_sitemap', 'ProductController@apiAddProductBySitemap')->name('api_add_product_by_sitemap');
            Route::post('/page/add_by_url_list', 'ProductController@apiAddProductByUrlList')->name('api_add_product_by_url_list');
            Route::post('/product/delete_product_image/', 'PartController@deletePageImage')->name('api_delete_product_image');

            //part
            Route::post('/part/register_images', 'PartController@apiRegisterImages')->name('register_images_parts');
            Route::post('/part/create', 'PartController@apiCreate')->name('api_part_create');
            Route::get('/part/{id}/basic_setting', 'PartController@apiGetPartBasicSetting')->name('api_get_part_basic_setting');
            Route::get('/part/{id}/design_setting', 'PartController@apiGetPartDesignSetting')->name('api_get_part_design_setting');
            Route::post('/part/{id}/update_basic_setting', 'PartController@updateBasicSetting')->name('api_update_basic_setting');
            Route::post('/part/{id}/update_part_design', 'PartController@updatePartDesign')->name('api_update_part_design');
            Route::get('/part/get_image_detail/{postId}', 'PartController@apiGetPartImageDetail')->name('api_get_part_image_detail');
            Route::get('/part/get_all_part', 'PartController@apiGetAllParts')->name('api_get_all_part');
            Route::get('/get_bind_product/{postId}', 'PartController@apiGetImageDetail')->name('api_get_vtdr_img_detail');
            Route::post('/part/register_image', 'PartController@registerImage')->name('api_register_image_part');
            Route::post('/part/{id}/publish', 'PartController@publish')->name('api_publish_part');
            //site
            Route::get('/get_list_product', 'SiteController@apiGetListProduct')->name('api_get_list_product');

            // post
            Route::get('/post/{id}/offer', 'OfferController@apiGetOfferDetail')->name('api_get_offer_detail');
            Route::get('/post/{id}/get_registered_part', 'PostController@apiGetRegisteredPart')->name('api_get_registered_part');
        });
    });
});

/** ADMIN */

Route::get('/admin/login', 'Admin\LoginController@loginPage')->name('admin_login');
Route::get('/admin/fb/callback', 'Admin\LoginController@fbCallback')->name('admin_fb_callback');
Route::get('/admin', function () {
    return redirect('/admin/login');
});

Route::group(['prefix' => 'admin', 'middleware' => 'auth:admin'], function () {

    Route::get('/', function () {
        return redirect()->route('admin_login');
    });
    Route::get('/logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
    Route::get('/logout', 'Admin\LoginController@logout')->name('admin_logout');
    Route::get('/offer_comments', 'Admin\OfferCommentController@index')->name('offer_comments');
    Route::get('/advertiser_offer/{advId}', 'Admin\OfferCommentController@getAdvertiserOffer')->name('advertiser_offer');
    Route::post('/changeAdvertiserOfferStatus', 'Admin\OfferCommentController@changeAdvertiserOfferStatus')->name('change_advertiser_offer_status');

    Route::group(['middleware' => 'auth.superadmin'], function () {

        Route::get('/dashboard', 'Admin\DashboardController@dashboard')->name('admin_dashboard');
        Route::post('/csv_download', 'Admin\DashboardController@exportCSV')->name('csv_download');

        Route::get('/tenant_list', 'Admin\TenantController@listPage')->name('tenant_list');
        Route::post('/create_tenant', 'Admin\TenantController@create')->name('create_tenant');

        Route::get('/account/list', 'Admin\UserController@listPage')->name('user_list');
        Route::get('/account/remove/{id}', 'Admin\UserController@remove')->name('remove_user');
        Route::post('/account/invite', 'Admin\UserController@invite')->name('invite_advertiser');
        Route::get('/account/login_as/{userId}', 'Admin\UserController@loginAsUser')->name('login_as_user');

        Route::get('/admin/list', 'Admin\AdminController@adminListPage')->name('admin_list');
        Route::get('/admin/remove/{id}', 'Admin\AdminController@remove')->name('remove_admin');
        Route::post('/admin/change_egc_setting', 'Admin\AdminController@changeEGCSetting')->name('change_egc_setting');
        Route::post('/admin/invite', 'Admin\AdminController@invite')->name('invite_admin');

        Route::get('/adaccount/list', 'Admin\AdAccountController@listPage')->name('adaccount_list');
        Route::get('/adaccount/login_as/{adAccountId}', 'Admin\AdAccountController@loginAsAdvertiser')->name('login_as_adaccount');
        Route::post('/adaccount/update_info', 'Admin\AdAccountController@updateInfo')->name('update_ad_account_info');
        Route::post('/adaccount/remove/{id}', 'Admin\AdAccountController@remove')->name('remove_ad_account');
        Route::post('/adaccount/create', 'Admin\AdAccountController@create')->name('create_advertiser');

        Route::get('/conversion/list', 'Admin\ConversionController@conversionLabelPage')->name('admin_conversion_setting');
        Route::post('/conversion/update', 'Admin\ConversionController@updateConversionLabel')->name('update_conversion_label');

        Route::get('/comment_template/list', 'Admin\CommentController@commentTemplatePage')->name('comment_template_setting');
        Route::get('/comment_template/remove/{id}', 'Admin\CommentController@removeCommentTemplate')->name('remove_comment_template');
        Route::post('/comment_template/update', 'Admin\CommentController@updateCommentTemplate')->name('update_comment_template');

        Route::get('/extend_function', 'Admin\ExtendFunctionController@extendFunctionPage')->name('extend_function');
        Route::post('/create_dynamic_creative', 'Admin\ExtendFunctionController@createDynamicCreative')->name('create_dynamic_creative');
        Route::get('/ad_videos/{adAccountId}', 'Admin\ExtendFunctionController@apiGetAdVideos')->name('api_get_ad_videos');
        Route::get('/instagram_actors/{adAccountId}', 'Admin\ExtendFunctionController@apiGetInstagramActors')->name('api_get_instagram_actor');

        Route::get('/hashtags', 'Admin\HashtagController@index')->name('admin_hashtag_list');
        Route::post('/hashtagCrawl', 'Admin\HashtagController@executeCrawlCommand')->name('execute_hashtag_command');
        Route::post('/hashtag/inactive', 'Admin\HashtagController@inactive')->name('inactive_hashtag');

        Route::get('/contract/{advId}', 'Admin\ContractController@detail')->name('contract_detail');
        Route::post('/contract/create', 'Admin\ContractController@create')->name('contract_create');
        Route::post('/contract/update', 'Admin\ContractController@update')->name('contract_update');
        Route::get('/contract/schedule/{advId}', 'Admin\ContractController@apiGetContractSchedule')->name('api_get_contract_schedule');
        Route::get('/contract/sync/{advId}', 'Admin\ContractController@apiSyncOwnedContract')->name('sync_owned_contract');
        Route::get('/get_latest_schedule/{contractId?}', 'Admin\ContractController@apiGetLatestSchedule')->name('get_latest_schedule');
        Route::get('advertiser/{advId}/contracts/', 'Admin\ContractController@apiGetAllContracts')->name('api_get_all_contract');
        Route::get('/sites', 'SiteController@apiGetAllSites')->name('api_get_all_site');
        Route::get('/site/{id?}', 'SiteController@apiGetSite')->name('api_get_site');
        Route::get('/part_requests', 'Admin\PartRequestController@index')->name('part_requests_list');
    });
});
