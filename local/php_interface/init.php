<?

// автолоадер проекта
require_once __DIR__ . '/../app/autoload.php';

require_once __DIR__ . '/src/autoload.php';

require_once __DIR__ . '/src/event_handler.php';

//require_once __DIR__ . '/src/Otus/Orm/PatientCardTable.php';
//require_once __DIR__ . '/src/Otus/Orm/PatientTable.php';

function vd($data)
{
    echo '<script>console.log(' . json_encode($data, JSON_UNESCAPED_UNICODE) . ');</script>';
}

?>