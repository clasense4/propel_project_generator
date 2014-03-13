<?php

namespace PropelProjectGen;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class PropelProjectGen extends Command {

    protected function configure()
    {
        // $propel = array();
        $propel['project'] = "demopropel";
        $propel['dbname'] = "demopropel";
        $propel['engine'] = "mysql";
        $propel['host'] = "localhost";
        $propel['port'] = "3306";
        $propel['user'] = "root";
        $propel['password'] = "54321";

        $this->setName("propel:gen")
             ->setDescription("This console will generate basic propel skeleton")
             ->setDefinition(array(
                      new InputOption('project', 'pr', InputOption::VALUE_OPTIONAL, 'Database Name (xond2)', $propel['project']),
                      new InputOption('dbname', 'db', InputOption::VALUE_OPTIONAL, 'Database Name (xond2)', $propel['dbname']),
                      new InputOption('engine', 'e', InputOption::VALUE_OPTIONAL, 'Database Engine (mysql)', $propel['engine']),
                      new InputOption('host', 'i', InputOption::VALUE_OPTIONAL, 'Ip (localhost)', $propel['host']),
                      new InputOption('port', 'p', InputOption::VALUE_OPTIONAL, 'Database Port (3306)', $propel['port']),
                      new InputOption('user', 'u', InputOption::VALUE_OPTIONAL, 'Database User (root)', $propel['user']),
                      new InputOption('password', 'pw', InputOption::VALUE_OPTIONAL, 'Database Password (12345)', $propel['password'])
                ))
             ->setHelp(<<<EOT
Still empty
EOT
);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // dialog helper
        $dialog = $this->getHelperSet()->get('dialog');

        // twig loader
        $basetpl = __DIR__.'../../../app/template/';
        $loader = new \Twig_Loader_Filesystem($basetpl);
        $twig = new \Twig_Environment($loader
        // DO NOT USE CACHE
        //     , array(
        //     'cache' => __DIR__.'../../../app/template/',
        // )
        );

        // symfony console formatter loader
        $header_style = new OutputFormatterStyle('white', 'green', array('bold'));
        $output->getFormatter()->setStyle('header', $header_style);

        // get param
        $propel['project'] = $input->getOption('project');
        $propel['dbname'] = $input->getOption('dbname');
        $propel['engine'] = $input->getOption('engine');
        $propel['host'] = $input->getOption('host');
        $propel['port'] = intval($input->getOption('port'));
        $propel['user'] = $input->getOption('user');
        $propel['password'] = $input->getOption('password');

        // create a dir
        $basedir = __DIR__.'../../../app/config/';
        if (!is_dir($basedir)){
            if (mkdir($basedir)) {
                $output->writeln('<header>Dir created</header>');
            }
        }

        // give some output
        $output->writeln('<header>project = '.$propel['project'].' </header>');
        $output->writeln('<header>dbname = '.$propel['dbname'].' </header>');
        $output->writeln('<header>Engine = '.$propel['engine'].' </header>');
        $output->writeln('<header>Host = '.$propel['host'].' </header>');
        $output->writeln('<header>Port = '.$propel['port'].' </header>');
        $output->writeln('<header>User = '.$propel['user'].' </header>');
        $output->writeln('<header>Password = '.$propel['password'].' </header>');
        // $output->writeln('<header>Current Dir  = '.__DIR__.' </header>');

        // let's ask, make sure the setting is right
        $ask = $dialog->ask(
            $output,
            'All good? [Y:n] ',
            'y'
        );

        if ($ask == 'y' ) {
            // lets write the file
            $string = $twig->render('build.properties.twig', array('propel' => $propel));
            $filename = $basedir.'build.properties';
            $fp = fopen($filename, 'w');
            if (!$fp) {
                $output->writeln('<header>File '.$filename.' failed to open</header>');
            }
            if (fwrite($fp, $string)) {
                $output->writeln('<header>File '.$filename.' written</header>');
            }
            fclose($fp);

            // lets write the file
            $string = $twig->render('runtime-conf.xml.twig', array('propel' => $propel));
            $filename = $basedir.'runtime-conf.xml';
            $fp = fopen($filename, 'w');
            if (!$fp) {
                $output->writeln('<header>File '.$filename.' failed to open</header>');
            }
            if (fwrite($fp, $string)) {
                $output->writeln('<header>File '.$filename.' written</header>');
            }
            fclose($fp);
        }


        // let's ask, write dummy schema.xml?
        $bundle = $dialog->ask(
            $output,
            'Want to include dummy schema.xml? [Y:n] ',
            'y'
        );

        if ($bundle == 'y') {
            // lets write the file
            $string = $twig->render('schema.xml.twig', array('propel' => $propel));
            $filename = $basedir.'schema.xml';
            $fp = fopen($filename, 'w');
            if (!$fp) {
                $output->writeln('<header>File '.$filename.' failed to open</header>');
            }
            if (fwrite($fp, $string)) {
                $output->writeln('<header>File '.$filename.' written</header>');
            }
            fclose($fp);
            $output->writeln('<header>Great, now you can play with propel-gen</header>');
        }

    }
}