<?php
declare(strict_types=1);

use App\Bootstrap;
use App\Router;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\TasksController;
use App\Http\Controllers\NotesController;
use App\Http\Controllers\ExportsController;
use App\Http\Controllers\PhotosController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\HealthController;

require_once __DIR__ . '/../vendor_stub.php';

$bootstrap = new Bootstrap();
$config = $bootstrap->config();

$router = new Router($config);

$router->add('GET', '/', [new HomeController(), 'index'], ['auth']);
$router->add('GET', '/login', [new AuthController(), 'showLogin']);
$router->add('POST', '/login', [new AuthController(), 'login'], ['rate', 'csrf']);
$router->add('POST', '/logout', [new AuthController(), 'logout'], ['auth', 'csrf']);
$router->add('GET', '/profile', [new UsersController(), 'profile'], ['auth']);
$router->add('GET', '/users', [new UsersController(), 'index'], ['auth']);
$inventoryController = new InventoryController();
$tasksController = new TasksController();
$router->add('GET', '/inventory', [$inventoryController, 'index'], ['auth']);
$router->add('GET', '/exports/inventory.csv', [$inventoryController, 'exportCsv'], ['auth']);
$router->add('GET', '/exports/inventory.xlsx', [$inventoryController, 'exportXlsx'], ['auth']);
$router->add('GET', '/tasks', [$tasksController, 'index'], ['auth']);
$router->add('GET', '/tasks/export/csv', [$tasksController, 'exportCsv'], ['auth']);
$router->add('GET', '/tasks/export/xlsx', [$tasksController, 'exportXlsx'], ['auth']);
$router->add('GET', '/notes', [new NotesController(), 'index'], ['auth']);
$router->add('POST', '/photos/presign', [new PhotosController(), 'presign'], ['auth', 'csrf']);
$router->add('GET', '/exports/tasks.pdf', [new ExportsController(), 'tasksPdf'], ['auth']);
$router->add('GET', '/api/v1/notifications', [new NotificationsController(), 'unread'], ['auth']);
$router->add('GET', '/healthz', [new HealthController(), 'index']);

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$router->dispatch($_SERVER['REQUEST_METHOD'], $path);
