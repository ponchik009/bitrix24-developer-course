<?

// автолоадер проекта
require_once __DIR__ . '/../app/autoload.php';

require_once __DIR__ . '/src/autoload.php';

require_once __DIR__ . '/src/event_handler.php';

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

function vd($data)
{
    echo '<script>console.log(' . json_encode($data, JSON_UNESCAPED_UNICODE) . ');</script>';
}

?>