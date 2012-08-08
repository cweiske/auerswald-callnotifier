<?php
namespace callnotifier;


class CLI
{
    protected $cliParser;

    public function __construct()
    {
        $this->setupCli();
    }

    public function run()
    {
        $config = new Config();
        try {
            $result = $this->cliParser->parse();
        } catch (\Exception $exc) {
            $this->cliParser->displayError($exc->getMessage());
        }

        $this->fillConfig($config, $result);

        $log = new Log();
        if ($result->options['debug'] || $result->options['debugEdss1']) {
            $debugLogger = new Logger_Debug();
            $log->addLogger($debugLogger, '*');
            if ($result->options['debugEdss1']) {
                $debugLogger->edss1MsgOnly = true;
            }
        }

        $callMonitor = new CallMonitor($config, $log);

        $configFile = $this->getConfigFile();
        if ($configFile !== null) {
            include $configFile;
        }

        $handler = new MessageHandler($config, $log, $callMonitor);

        if ($config->replayFile !== null) {
            $sourceClass = 'callnotifier\Source_File';
        } else {
            $sourceClass = 'callnotifier\Source_Remote';
        }

        try {
            $source = new $sourceClass($config, $handler);
            $source->run();
        } catch (\Exception $e) {
            $msg = 'Callnotifier error!' . "\n"
                . 'Code: ' . $e->getCode() . "\n"
                . 'Message: ' . $e->getMessage() . "\n";
            file_put_contents('php://stderr', $msg);
            exit(1);
        }
    }

    public function setupCli()
    {
        $p = new \Console_CommandLine();
        $p->description = 'Notifies about incoming calls on an Auerswald COMpact 3000';
        $p->version = '0.0.1';

        $p->addOption(
            'host',
            array(
                'short_name'  => '-h',
                'long_name'   => '--host',
                'description' => 'IP of COMpact 3000',
                'help_name'   => 'IP',
                'action'      => 'StoreString'
            )
        );

        $p->addOption(
            'dumpFile',
            array(
                'long_name'   => '--dump',
                'description' => 'dump messages into file for later replay',
                'help_name'   => 'FILE',
                'action'      => 'StoreString'
            )
        );
        $p->addOption(
            'replayFile',
            array(
                'long_name'   => '--replay',
                'description' => "Replay messages from file instead from network",
                'help_name'   => 'FILE',
                'action'      => 'StoreString'
            )
        );

        $p->addOption(
            'debug',
            array(
                'short_name'  => '-d',
                'long_name'   => '--debug',
                'description' => "Debug mode: Echo all received messages and events",
                'action'      => 'StoreTrue'
            )
        );
        $p->addOption(
            'debugEdss1',
            array(
                'short_name'  => '-e',
                'long_name'   => '--debug-edss1',
                'description' => "Debug mode: Show EDSS1 messages only",
                'action'      => 'StoreTrue'
            )
        );

        $this->cliParser = $p;
    }

    protected function fillConfig($config, $result)
    {
        $config->setIfNotEmpty('host', $result->options['host']);
        $config->setIfNotEmpty('dumpFile', $result->options['dumpFile']);
        $config->setIfNotEmpty('replayFile', $result->options['replayFile']);
    }

    /**
     * Finds the path to the configuration file.
     *
     * The following locations are tried:
     * - Git checkout: data/callnotifier.config.php
     * - ~/.config/callnotifier.config.php
     * - /etc/callnotifier.config.php
     *
     * @return string Full path of config file or NULL if no file found
     */
    protected function getConfigFile()
    {
        if (basename(dirname(__DIR__)) == 'src'
            && file_exists(__DIR__ . '/../../data/callnotifier.config.php')
        ) {
            return __DIR__ . '/../../data/callnotifier.config.php';
        }

        if (isset($_ENV['HOME'])) {
            $file = $_ENV['HOME'] . '/.config/callnotifier.config.php';
            if (file_exists($file)) {
                return $file;
            }
        }

        $file = '/etc/callnotifier.config.php';
        if (file_exists($file)) {
            return $file;
        }

        return null;
    }
}

?>