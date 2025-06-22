<?

namespace Demo\Logger;

use Bitrix\Main\Diag\ExceptionHandlerLog;
use Bitrix\Main\Diag\FileLogger;
use Bitrix\Main\Diag\ExceptionHandlerFormatter;

use Bitrix\Main;
use Psr\Log;

class FileExceptionHandlerLogCustom extends ExceptionHandlerLog
{
	const MAX_LOG_SIZE = 1000000;
	const DEFAULT_LOG_FILE = "local/logs/exceptions.log";

	private $level;

	/** @var Log\LoggerInterface */
	protected $logger;

	public function initialize(array $options)
	{
		$logFile = static::DEFAULT_LOG_FILE;
		if (isset($options["file"]) && !empty($options["file"]))
		{
			$logFile = $options["file"];
		}

		if ((!str_starts_with($logFile, "/")) && !preg_match("#^[a-z]:/#", $logFile))
		{
			$logFile = Main\Application::getDocumentRoot()."/".$logFile;
		}

		$maxLogSize = static::MAX_LOG_SIZE;
		if (isset($options["log_size"]) && $options["log_size"] > 0)
		{
			$maxLogSize = (int)$options["log_size"];
		}

		$this->logger = new FileLogger($logFile, $maxLogSize);

		if (isset($options["level"]) && $options["level"] > 0)
		{
			$this->level = (int)$options["level"];
		}
	}

	/**
	 * @param \Throwable $exception
	 * @param int $logType
	 */
	public function write($exception, $logType)
	{
		$text = ExceptionHandlerFormatter::format($exception, false, $this->level);

		$context = [
			'type' => static::logTypeToString($logType),
		];

		$logLevel = static::logTypeToLevel($logType);

		$message = "OTUS // {date} - Host: {host} - {type} - {$text}\n";

		$this->logger->log($logLevel, $message, $context);
	}

	/**
	 * @deprecated
	 */
	protected function writeToLog($text)
	{
		$this->logger->debug($text);
	}
}

?>