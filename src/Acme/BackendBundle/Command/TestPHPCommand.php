<?php

namespace Acme\BackendBundle\Command;

use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Acme\BackendBundle\Services\UtilService;
use Symfony\Component\Process\Process;
use Acme\MayaBundle\Entity\ItinerarioSimple;
use Acme\MayaBundle\Entity\ItinerarioEspecial;

class TestPHPCommand extends ContainerAwareCommand{
   
    protected function configure()
    {
        $this
            ->setName('SISTEMA:MyTest')
            ->setDefinition(array(
                new InputArgument(
                    'nameArgument',
                    InputArgument::OPTIONAL,
                    'descripcionArgument',
                    'defaultValueArgument'
                ),
                new InputOption(
                     'nameOption',
                     null,
                     InputOption::VALUE_OPTIONAL,
                     'descripcionOption',
                     'defaultValueOption'
                 ),
            ))
            ->setDescription('Comando para probar metodos.')
            ->setHelp(<<<EOT
Comando para probar metodos.
EOT
        );
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Testing method ----- init');
        $contenedor = $this->getContainer();
        $contenedor->enterScope('request');
        $server = array('REMOTE_ADDR' => "127.0.0.1");
        $request =  new Request();
        $request->initialize(array(), array(), array(), array(), array(), $server);
        $request->setMethod("None");
        $request->setSession(new Session(new MockArraySessionStorage()));
        $contenedor->set('request', $request, 'request');
        $em = $contenedor->get('doctrine')->getManager();
        
        //$contenedor->get('acme_scheduler.scheduler')->executeJobs();
//        $contenedor->get('acme_backend_sincronizar')->setScheduledJob();
//        $contenedor->get('acme_backend_mensaje')->setScheduledJob();
            
        UtilService::sendEmail($contenedor, "hola", "javiermarti84@gmail.com", "Ejemplo1");
        
//        $def = $contenedor->getParameter('facebook_scope');
//        var_dump($def);
                
//        $path = "C:\wamp\www\PORTAL.WEB\src\Acme\MayaBundle\Entity/../../../../web/uploads/3d7265ed889d4f2d51d0d6c2d41f6af4effe38b9.jpeg";
//        chmod( $path, 0777 );
//        var_dump(is_writable($path));
//        var_dump(file_exists($path));
        
//        $isOK = false;
//        $fp = fopen($path,"w");
//        if ($fp !== false) {
//            $isOK = true;
//        }
//        fclose($fp);
//        unlink($path);
        
//        $contenedor->get('acme_backend_sincronizar')->setScheduledJob();
//        $contenedor->get('acme_backend_conexion')->setScheduledJob();
      
//        $id = 6;
//        $cantidad = 3;
//        $base = array(1,2,3,4,5,6,7);
//        var_dump(array_slice($base, $id-1, $cantidad));
        
//        $result = $contenedor->get('doctrine')->getRepository('Acme\BackendBundle\Entity\User')->findOneBy(array("usernameCanonical" => "javiermarti84"));
//        var_dump($result);
       
        $output->writeln('Testing method ----- end');
    }
    

}

?>
