<?php
//
//use Illuminate\Support\Facades\Route;
//use App\Http\Controllers\Auth\RegisteredUserController;
//use App\Http\Controllers\Auth\AuthenticatedSessionController;
//use App\Http\Controllers\CompanyController;
//use App\Http\Controllers\ContactController;
//use App\Http\Controllers\CustomerController;
//use App\Http\Controllers\CategoryController;
//use App\Http\Controllers\AttributeController;
//use App\Http\Controllers\WarehouseController;
//use App\Http\Controllers\AuditController;
//use App\Http\Controllers\ProductController;
//use App\Http\Controllers\ProductMediaController;
//use App\Http\Controllers\{
//    ProjectController, ColumnController, KanbanController, TaskController,
//    TimerController, TaskFileController, TaskCommentController
//};
//use App\Http\Controllers\SettingsController;
//use App\Http\Controllers\Settings\ProjectTaxonomyController;
//use App\Http\Controllers\Tasks\TaskTaxonomyController;
//
//
//
//
//Route::middleware('guest')->group(function () {
//    Route::get('/login',  [AuthenticatedSessionController::class, 'create'])->name('login');
//    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
//
//    Route::get('/register',  [RegisteredUserController::class, 'create'])->name('register');
//    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
//});
//
//Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
//    ->middleware('auth')
//    ->name('logout');
//
//Route::get('/', fn () => redirect()->route('login'));
//
//Route::middleware('auth')->group(function () {
//    // dashboard — один раз
//    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');
//
//    Route::resource('companies', CompanyController::class);
//    Route::resource('contacts', ContactController::class);
//    Route::resource('customers', CustomerController::class);
//    Route::resource('categories', CategoryController::class)->except(['show']);
//    Route::resource('attributes', AttributeController::class)->except(['show']);
//    Route::resource('warehouses', WarehouseController::class);
//    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
//    Route::resource('products', ProductController::class);
//
//    Route::post('uploads/products', [ProductMediaController::class, 'upload'])->name('products.upload');
//    Route::delete('uploads/products/{image}', [ProductMediaController::class, 'destroy'])->whereNumber('image')->name('products.upload.delete');
//
//    Route::get('/tasks/kanban/{board?}', [KanbanController::class, 'show'])->whereNumber('board')->name('kanban.show');
//
//    // ===== Проекты =====
//    Route::get('/projects',             [ProjectController::class, 'index'])->name('projects.index');
//    Route::post('/projects',            [ProjectController::class, 'store'])->name('projects.store');
//    Route::get('/projects/{project}',   [ProjectController::class, 'show'])->whereNumber('project')->name('projects.show');
//    Route::patch('/projects/{project}', [ProjectController::class, 'update'])->whereNumber('project')->name('projects.update');
//    Route::delete('/projects/{project}',[ProjectController::class, 'destroy'])->whereNumber('project')->name('projects.destroy');
//
//    // Колонки (AJAX)
//    Route::post('/projects/{project}/columns',         [ColumnController::class, 'store'])->whereNumber('project')->name('columns.store');
//    Route::patch('/columns/{column}',                  [ColumnController::class, 'update'])->whereNumber('column')->name('columns.update');
//    Route::delete('/columns/{column}',                 [ColumnController::class, 'destroy'])->whereNumber('column')->name('columns.destroy');
//    Route::post('/projects/{project}/columns/reorder', [ColumnController::class, 'reorder'])->whereNumber('project')->name('columns.reorder');
//
//    // ===== Задачи / канбан =====
//    // ВАЖНО: спец-роут без ID должен идти ПЕРВЫМ
//    Route::post('/tasks/move', [TaskController::class, 'move'])->name('tasks.move');
//
//    Route::post('/tasks',                  [TaskController::class, 'store'])->name('tasks.store');
//    Route::get('/tasks/{task}',            [TaskController::class, 'show'])->whereNumber('task')->name('tasks.show');
//    Route::post('/tasks/{task}',          [TaskController::class, 'update'])->whereNumber('task')->name('tasks.update');
//
//    Route::post('/tasks/{task}/timer/start',[TimerController::class,'start'])->whereNumber('task')->name('kanban.timer.start');
//    Route::post('/tasks/{task}/timer/stop', [TimerController::class,'stop'])->whereNumber('task')->name('kanban.timer.stop');
//    Route::get('/timer/active',             [TimerController::class,'active'])->name('kanban.timer.active');
//
//    Route::post('/tasks/{task}/files',    [TaskFileController::class,'store'])->whereNumber('task')->name('tasks.files.store');
//    Route::delete('/files/{file}',        [TaskFileController::class,'destroy'])->whereNumber('file')->name('tasks.files.delete');
//    Route::delete('/tasks/{task}',       [TaskController::class, 'destroy'])->name('tasks.destroy');
//
//    Route::post('/tasks/{task}/comments', [TaskCommentController::class,'store'])->whereNumber('task')->name('tasks.comments.store');
//
//    // Если используете отдельные эндпоинты для загрузчика файлов:
//    Route::post('/task-files/upload',                    [TaskFileController::class, 'upload'])->name('task-files.upload');
//    Route::delete('/task-files/{attachment}',            [TaskFileController::class, 'destroy'])->whereNumber('attachment')->name('task-files.destroy');
//    Route::delete('/task-files/{attachment}',            [TaskFileController::class, 'destroyDraft'])->whereNumber('attachment')->name('task-files.destroyDraft');
//
//    Route::delete('/timers/{timer}', [TimerController::class, 'destroy'])
//        ->name('timers.destroy');
//
//    Route::middleware('auth')->group(function () {
//        Route::get('/settings/{section?}', [SettingsController::class, 'index'])
//            ->where('section', 'general|projects')
//            ->name('settings.index');
//    });
//
//    Route::post('/settings/general', [SettingsController::class, 'saveGeneral'])
//        ->name('settings.general.save');
//    Route::post('/settings/logo', [SettingsController::class, 'uploadLogo'])
//        ->name('settings.logo.upload');
//
//    Route::delete('/settings/logo', [SettingsController::class, 'deleteLogo'])
//        ->name('settings.logo.delete');
//
//    Route::post('/settings/projects/save', [SettingsController::class, 'saveProjects'])->name('settings.projects.save');
//
//    Route::prefix('settings/projects')->middleware(['auth'])->group(function () {
//        Route::post('/taxonomy/{group}', [ProjectTaxonomyController::class, 'save'])
//            ->name('settings.projects.taxonomy.save');
//    });
//
//
//    Route::middleware(['auth'])->group(function () {
//        Route::post('/tasks/{task}/taxonomy/sync', [TaskTaxonomyController::class, 'sync'])
//            ->name('tasks.taxonomy.sync');
//    });
//
//    Route::patch('/tasks/{task}/complete', [TaskController::class, 'markComplete'])
//        ->middleware('auth')
//        ->name('tasks.complete');
//
//
//});



use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductMediaController;

use App\Http\Controllers\{
    ProjectController,
    ColumnController,
    KanbanController,
    TaskController,
    TimerController,
    TaskFileController,
    TaskCommentController
};

use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Settings\ProjectTaxonomyController;
use App\Http\Controllers\Tasks\TaskTaxonomyController;

/*
|--------------------------------------------------------------------------
| Гостевые роуты (аутентификация)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');

    Route::get('/register',  [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/', fn () => redirect()->route('login'));

/*
|--------------------------------------------------------------------------
| Приватные роуты (под auth)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // ---- Dashboard
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    // ---- CRUD прочих сущностей
    Route::resource('companies', CompanyController::class);
    Route::resource('contacts', ContactController::class);
    Route::resource('customers', CustomerController::class);
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('attributes', AttributeController::class)->except(['show']);
    Route::resource('warehouses', WarehouseController::class);
    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
    Route::resource('products', ProductController::class);

    // Медиа для товаров
    Route::post('uploads/products', [ProductMediaController::class, 'upload'])->name('products.upload');
    Route::delete('uploads/products/{image}', [ProductMediaController::class, 'destroy'])
        ->whereNumber('image')->name('products.upload.delete');

    // ---- Канбан общесистемный
    Route::get('/tasks/kanban/{board?}', [KanbanController::class, 'show'])
        ->whereNumber('board')
        ->name('kanban.show');

    // =====================================================================
    // Проекты
    // =====================================================================
    Route::get('/projects',             [ProjectController::class, 'index'])->name('projects.index');
    Route::post('/projects',            [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}',   [ProjectController::class, 'show'])->whereNumber('project')->name('projects.show');
    Route::patch('/projects/{project}', [ProjectController::class, 'update'])->whereNumber('project')->name('projects.update');
    Route::delete('/projects/{project}',[ProjectController::class, 'destroy'])->whereNumber('project')->name('projects.destroy');

    // Колонки проекта (AJAX)
    Route::post('/projects/{project}/columns',         [ColumnController::class, 'store'])->whereNumber('project')->name('columns.store');
    Route::patch('/columns/{column}',                  [ColumnController::class, 'update'])->whereNumber('column')->name('columns.update');
    Route::delete('/columns/{column}',                 [ColumnController::class, 'destroy'])->whereNumber('column')->name('columns.destroy');
    Route::post('/projects/{project}/columns/reorder', [ColumnController::class, 'reorder'])->whereNumber('project')->name('columns.reorder');

    // =====================================================================
    // Задачи / Канбан
    // =====================================================================

    // Спец-роут без ID должен идти ПЕРВЫМ
    Route::post('/tasks/move', [TaskController::class, 'move'])->name('tasks.move');

    // Создание и просмотр задачи
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])
        ->whereNumber('task')->name('tasks.show');

    /**
     * Все мутации конкретной задачи — под task.lock
     * (запрещаем правки, если задача помечена complete=true)
     */
    Route::prefix('/tasks/{task}')
        ->whereNumber('task')
        ->middleware('task.lock')
        ->group(function () {
            // Обновление и удаление
            Route::post('/',   [TaskController::class, 'update'])->name('tasks.update');   // фронт шлёт POST
            Route::delete('/', [TaskController::class, 'destroy'])->name('tasks.destroy');

            // Таймеры
            Route::post('/timer/start', [TimerController::class, 'start'])->name('kanban.timer.start');
            Route::post('/timer/stop',  [TimerController::class, 'stop'])->name('kanban.timer.stop');

            // Файлы (загрузка из формы задачи)
            Route::post('/files', [TaskFileController::class,'store'])->name('tasks.files.store');

            // Комментарии
            Route::post('/comments', [TaskCommentController::class,'store'])->name('tasks.comments.store');

            // Таксономии задачи (произвольные метки / оценка)
            Route::post('/taxonomy/sync', [TaskTaxonomyController::class, 'sync'])
                ->name('tasks.taxonomy.sync');
        });

    // Отметить задачу выполненной / вернуть в работу — БЕЗ миддлвари,
    // чтобы можно было снять блокировку
    Route::patch('/tasks/{task}/complete', [TaskController::class, 'markComplete'])
        ->whereNumber('task')->name('tasks.complete');

    // Служебные маршруты таймера и файлов (не привязаны к {task})
    Route::get('/timer/active', [TimerController::class, 'active'])->name('kanban.timer.active');
    Route::delete('/timers/{timer}', [TimerController::class, 'destroy'])->name('timers.destroy');

    // Удаление файла по ID (вне /tasks/{task})
    Route::delete('/files/{file}', [TaskFileController::class,'destroy'])
        ->whereNumber('file')->name('tasks.files.delete');

    // Отдельные эндпоинты загрузчика (черновики, drag&drop и т.п.)
    Route::post('/task-files/upload', [TaskFileController::class, 'upload'])->name('task-files.upload');
    Route::delete('/task-files/{attachment}', [TaskFileController::class, 'destroy'])
        ->whereNumber('attachment')->name('task-files.destroy');
    // Отдельный URL для удаления черновых вложений (не конфликтует с destroy)
    Route::delete('/task-files/draft/{attachment}', [TaskFileController::class, 'destroyDraft'])
        ->whereNumber('attachment')->name('task-files.destroyDraft');

    // =====================================================================
    // Настройки
    // =====================================================================
    Route::get('/settings/{section?}', [SettingsController::class, 'index'])
        ->where('section', 'general|projects')
        ->name('settings.index');

    Route::post('/settings/general', [SettingsController::class, 'saveGeneral'])
        ->name('settings.general.save');
    Route::post('/settings/logo', [SettingsController::class, 'uploadLogo'])
        ->name('settings.logo.upload');
    Route::delete('/settings/logo', [SettingsController::class, 'deleteLogo'])
        ->name('settings.logo.delete');

    // (Для сохранения старого JSON-формата «projects», если используете)
    Route::post('/settings/projects/save', [SettingsController::class, 'saveProjects'])
        ->name('settings.projects.save');

    // Таксономии проектов (департаменты, типы, важности, метки, оценки)
    Route::prefix('settings/projects')->group(function () {
        Route::post('/taxonomy/{group}', [ProjectTaxonomyController::class, 'save'])
            ->name('settings.projects.taxonomy.save');
    });
});
