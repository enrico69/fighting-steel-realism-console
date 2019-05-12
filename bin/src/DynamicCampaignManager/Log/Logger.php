<?php
/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       12/05/2019 (dd-mm-YYYY)
 */
namespace App\DynamicCampaignManager\Log;

use Psr\Log\LoggerInterface;
use Symfony\Bridge\Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Symfony\Component\HttpFoundation\RequestStack;

class Logger
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    private $operationLogger;

    /**
     * @var null|\Symfony\Bridge\Monolog\Logger
     */
    private $scenarioLogger;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $request;

    /**
     * @var string
     */
    private $logDir;

    /**
     * Logger constructor.
     *
     * @param \Psr\Log\LoggerInterface                       $logger
     * @param string                                         $rootDir
     * @param \Symfony\Component\HttpFoundation\RequestStack $request
     *
     * @throws \Exception
     */
    public function __construct(LoggerInterface $logger, string $rootDir, RequestStack $request)
    {
        $this->logger = $logger;

        $this->logDir = $rootDir . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR;
        $this->operationLogger = new MonologLogger('operations');
        $this->operationLogger->pushHandler(new StreamHandler($this->logDir . 'operations.log'));

        $this->request = $request;
    }

    /**
     * @param string $message
     * @param string $level
     */
    public function operation(string $message, string $level = 'debug') : void
    {
        $method = $this->getLevel($level);
        $this->operationLogger->$method($message);
    }

    /**
     * @param string $message
     * @param string $level
     *
     * @throws \Exception
     */
    public function scenario(string $message, string $level = 'debug') : void
    {
        $method = $this->getLevel($level);

        if (null === $this->scenarioLogger) {
            $scenario = $this->request->getCurrentRequest()->query->get('scenario');
            if (!$scenario) {
                throw new \LogicException('Missing scenario name. Are you sure you really wanted to use this logging method?');
            }

            $this->scenarioLogger = new MonologLogger('scenario');
            $this->scenarioLogger->pushHandler(new StreamHandler($this->logDir . $scenario . '.log'));
        }

        $this->scenarioLogger->$method($message);
    }

    /**
     * @param string $message
     */
    public function error(string $message) : void
    {
        $this->logger->error($message);
    }

    /**
     * @param string $message
     */
    public function info(string $message) : void
    {
        $this->logger->info($message);
    }

    /**
     * @param string $message
     */
    public function debug(string $message) : void
    {
        $this->logger->debug($message);
    }

    /**
     * @param string $message
     */
    public function warning(string $message) : void
    {
        $this->logger->warning($message);
    }

    /**
     * @param string $level
     * @return string
     */
    private function getLevel(string $level) : string
    {
        switch ($level) {
            case 'debug': $method = 'debug'; break;
            case 'warning': $method = 'warning'; break;
            case 'error': $method = 'error'; break;
            default: $method = 'debug'; break;
        }

        return $method;
    }
}