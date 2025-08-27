<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Settings\ProjectTaxonomyController;
use App\Http\Controllers\Settings\users\UsersSettingsController as UsersCtrl;
use App\Http\Controllers\Settings\users\RolesController       as RolesCtrl;

// всё под auth; granular доступ — через middleware access
Route::middleware('auth')->group(function () {

    // экран настроек — просмотр
    Route::get('/settings', [SettingsController::class,'index'])
        ->middleware('access:settings,view')
        ->name('settings.index');

    // общие/логотип/проекты — только full
    Route::post('/settings/general', [SettingsController::class, 'saveGeneral'])
        ->middleware('access:settings,full')->name('settings.general.save');

    Route::post('/settings/logo', [SettingsController::class, 'uploadLogo'])
        ->middleware('access:settings,full')->name('settings.logo.upload');

    Route::delete('/settings/logo', [SettingsController::class, 'deleteLogo'])
        ->middleware('access:settings,full')->name('settings.logo.delete');

    Route::post('/settings/projects/save', [SettingsController::class, 'saveProjects'])
        ->middleware('access:settings,full')->name('settings.projects.save');

    Route::post('/settings/projects/taxonomy/{group}', [ProjectTaxonomyController::class, 'save'])
        ->whereAlpha('group')
        ->middleware('access:settings,full')
        ->name('settings.projects.taxonomy.save');

    // Users JSON endpoints
    Route::get(   '/settings/users',               [UsersCtrl::class,'usersIndex'])
        ->middleware('access:settings,view')->name('settings.users.users.index');
    Route::post(  '/settings/users',               [UsersCtrl::class,'usersStore'])
        ->middleware('access:settings,full')->name('settings.users.users.store');
    Route::patch( '/settings/users/{user}',        [UsersCtrl::class,'usersUpdate'])
        ->whereNumber('user')->middleware('access:settings,full')->name('settings.users.users.update');
    Route::patch( '/settings/users/{user}/block',  [UsersCtrl::class,'usersBlockToggle'])
        ->whereNumber('user')->middleware('access:settings,full')->name('settings.users.users.block');
    Route::delete('/settings/users/{user}',        [UsersCtrl::class,'usersDestroy'])
        ->whereNumber('user')->middleware('access:settings,full')->name('settings.users.users.destroy');

    // Groups JSON endpoints (тем же контроллером)
    Route::get(   '/settings/groups',              [UsersCtrl::class,'groupsIndex'])
        ->middleware('access:settings,view')->name('settings.users.groups.index');
    Route::post(  '/settings/groups',              [UsersCtrl::class,'groupsStore'])
        ->middleware('access:settings,full')->name('settings.users.groups.store');
    Route::patch( '/settings/groups/{group}',      [UsersCtrl::class,'groupsUpdate'])
        ->whereNumber('group')->middleware('access:settings,full')->name('settings.users.groups.update');
    Route::delete('/settings/groups/{group}',      [UsersCtrl::class,'groupsDestroy'])
        ->whereNumber('group')->middleware('access:settings,full')->name('settings.users.groups.destroy');

    // Roles JSON endpoints
    Route::get(   '/settings/roles',               [RolesCtrl::class,'index'])
        ->middleware('access:settings,view')->name('settings.users.roles.index');
    Route::post(  '/settings/roles',               [RolesCtrl::class,'store'])
        ->middleware('access:settings,full')->name('settings.users.roles.store');
    Route::patch( '/settings/roles/{role}',        [RolesCtrl::class,'update'])
        ->whereNumber('role')->middleware('access:settings,full')->name('settings.users.roles.update');
    Route::delete('/settings/roles/{role}',        [RolesCtrl::class,'destroy'])
        ->whereNumber('role')->middleware('access:settings,full')->name('settings.users.roles.destroy');
});
