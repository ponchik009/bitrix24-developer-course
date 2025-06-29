<?

// автолоадер проекта
include_once __DIR__ . '/../app/autoload.php';

function vd($data)
{
    echo '<script>console.log(' . json_encode($data, JSON_UNESCAPED_UNICODE) . ');</script>';
}

?>