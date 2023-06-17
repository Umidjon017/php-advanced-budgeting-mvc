<?php declare(strict_types = 1);

use App\App;
use App\Config;
use App\Router;
use App\Controllers\HomeController;
use App\Controllers\TransactionController;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

define('APP_PATH', __DIR__ . '/../app' . DIRECTORY_SEPARATOR);
define('STORAGE_PATH', __DIR__ . '/../storage' . DIRECTORY_SEPARATOR);
define('VIEW_PATH', __DIR__ . '/../views' . DIRECTORY_SEPARATOR);

$router = new Router();

$router
    ->get('/', [HomeController::class, 'index'])
    ->get('/transactions', [TransactionController::class, 'index'])
    ->post('/transaction-upload', [TransactionController::class, 'upload']);

(new App(
    $router,
    ['uri' => $_SERVER['REQUEST_URI'], 'method' => $_SERVER['REQUEST_METHOD']],
    new Config($_ENV)
))->run();

