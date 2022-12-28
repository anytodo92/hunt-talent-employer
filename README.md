# 【HutJob】Login, TOP
## feature/registration

# 【HuntJob】Password Reset
## feature/password-reminder

# 【HuntJob】Common Header/Footer
## feature/common-header-footer

# 【HuntJob】Worker List QA
## feature/offer-info-qanda

# inCul rank of engineer

## Server Summary
    PHP7.4
    MySQL5.7, MariaDB10
    
## Library
    Laravel8.35.1 (vue.js)
    Bootstrap4
    jQuery3.5.1
    vue.js v3

# Environment

## Database
Generate the table by migration.

    php artisan migrate
    
Generate the general user and admin user.

    php artisan db:seed

# Patch Process

    php artisan schedule:work

# Source Description

## Helper

Add app/Helpers/Helper.php

Adde below code into app/Providers/AppServiceProvider.php 

    // Custom Helper functions
    $file = app_path('Helpers/Helper.php');
    if (file_exists($file)) {
        require_once($file);
    }

## Constants & Enum

Constant defined as a glass

    app/Constants.php
    
Enum defined as config (config/constants.php)

    ...
    
    // User type
    'user_types' => [
        \App\Constants::USER_TYPE_COMPANY   => 'Recruiting company',
        \App\Constants::USER_TYPE_RECRUIT   => 'Recruitment',
        \App\Constants::USER_TYPE_OUTSOURCE => 'Outsourcing SES',
        \App\Constants::USER_TYPE_ADMIN     => 'Operating company',
    ],
    
    ...

Helperに「g_num」という関数がある。

    // Get display string from number
    g_num('user_types', Constants::USER_TYPE_COMPANY);

    // Get all items for list display
    $userTypes = g_num('user_types');
    foreach ($userTypes as $key => $val) {
        ...
    }
