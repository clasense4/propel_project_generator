<?php

namespace PropelProjectGen;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class PropelProjectGen extends Command 
{
    /**
     * Render a file
     * @param  [string] $twigname [twig filename]
     * @param  [string] $path     [path from project root ex. app/config/ ]
     * @param  [string] $filename [output filename ex. test.twig]
     * @param  [array] $propel   [array to pass into twig]
     * @param  [Twig Class] $twig     [Twig Class]
     * @param  [OutputInterface] $output   [Symfony Console Output Interface]
     * @return [OutputInterface]           [Write output interface]
     */
    public function render($twigname, $path, $filename, $propel, $twig, $output)
    {
        // lets write the file
        $project_dir = __DIR__.'/../../';
        $string = $twig->render($twigname, array('propel' => $propel));
        $file = $project_dir.$path.$filename;

        if (FALSE == ($fp = fopen($file, 'w'))) {
            $output->writeln('<header>File '.$file.' failed to open</header>');
        }
        if (fwrite($fp, $string)) {
            $output->writeln('<header>File '.$file.' written</header>');
        }
        fclose($fp);
    }

    protected function configure()
    {
        // $propel = array();
        $propel['project'] = "demopropel";
        $propel['dbname'] = "demopropel";
        $propel['engine'] = "mysql";
        $propel['host'] = "127.0.0.1";
        $propel['port'] = "3306";
        $propel['user'] = "root";
        $propel['password'] = "12345";

        $this->setName("propel:gen")
             ->setDescription("This console will generate basic propel skeleton")
             ->setDefinition(array(
                      new InputOption('project', 'P', InputOption::VALUE_OPTIONAL, 'Database Name (demopropel)', $propel['project']),
                      new InputOption('dbname', 'D', InputOption::VALUE_OPTIONAL, 'Database Name (demopropel)', $propel['dbname']),
                      new InputOption('engine', 'E', InputOption::VALUE_OPTIONAL, 'Database Engine (mysql)', $propel['engine']),
                      new InputOption('host', 'H', InputOption::VALUE_OPTIONAL, 'Ip (localhost)', $propel['host']),
                      new InputOption('port', 'r', InputOption::VALUE_OPTIONAL, 'Database Port (3306)', $propel['port']),
                      new InputOption('user', 'U', InputOption::VALUE_OPTIONAL, 'Database User (root)', $propel['user']),
                      new InputOption('password', 'p', InputOption::VALUE_OPTIONAL, 'Database Password (12345)', $propel['password']),
                ))
             ->setHelp(<<<EOT
Generate Propel 1.x basic project skeleton

Basic Usage :

<info>app/console propel:gen</info>

Override setting :

<info>app/console propel:gen -P 'test' -D 'test' -H '127.0.0.1' -U 'root' -P '54321'</info>

EOT
);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // dialog helper
        $dialog = $this->getHelperSet()->get('dialog');

        // twig loader
        $basetpl = __DIR__.'/../../app/template/';
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
        $basedir = __DIR__.'/../../app/config/';
        if (!is_dir($basedir)){
            if (mkdir($basedir)) {
                $output->writeln('<header>Dir created</header>');
            }
        }

        // give some output
        $output->writeln('project   = <header>'.$propel['project'].'</header>');
        $output->writeln('dbname    = <header>'.$propel['dbname'].'</header>');
        $output->writeln('Engine    = <header>'.$propel['engine'].'</header>');
        $output->writeln('Host      = <header>'.$propel['host'].'</header>');
        $output->writeln('Port      = <header>'.$propel['port'].'</header>');
        $output->writeln('User      = <header>'.$propel['user'].'</header>');
        $output->writeln('Password  = <header>'.$propel['password'].'</header>');
        // $output->writeln('<header>Current Dir  = '.__DIR__.' </header>');

        // let's ask, make sure the setting is right
        $ask = $dialog->ask(
            $output,
            'All good? [Y:n] ',
            'y'
        );

        if ($ask == 'y' ) {
            // write build.properties
            $this->render('build.properties.twig', 'app/config/', 'build.properties', $propel, $twig, $output);

            //write runtime-conf.xml
            $this->render('runtime-conf.xml.twig', 'app/config/', 'runtime-conf.xml', $propel, $twig, $output);

        }
        else {
            exit;
        }


        // let's ask, write dummy schema.xml?
        $bundle = $dialog->ask(
            $output,
            'Want to include dummy schema.xml? [Y:n] ',
            'y'
        );

        if ($bundle == 'y') {
            // write dummy schema.xml
            $this->render('schema.xml.twig', 'app/config/', 'schema.xml', $propel, $twig, $output);
            // some info
            $output->writeln('Great, now you can play with <header>propel-gen</header>');
        }
        else {
            exit;
        }

        // some info
        $output->writeln('After you have <header>schema.xml</header> you can use <header>propel-gen</header>');
    }
}