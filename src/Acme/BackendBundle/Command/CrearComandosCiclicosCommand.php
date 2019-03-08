<?php

namespace Acme\BackendBundle\Command;

use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class CrearComandosCiclicosCommand extends ContainerAwareCommand{
    
    protected function configure()
    {
        $this
            ->setName('SISTEMA:Crea los comandos ciclicos del sistema')
            ->setDefinition(array())
            ->setDescription('Crea los comandos.')
            ->setHelp(<<<EOT
Crea los comandos.
EOT
        );
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $contenedor = $this->getContainer();
        $contenedor->enterScope('request');
        $server = array('REMOTE_ADDR' => "127.0.0.1");
        $request =  new Request();
        $request->initialize(array(), array(), array(), array(), array(), $server);
        $request->setMethod("None");
        $request->setSession(new Session(new MockArraySessionStorage()));
        $contenedor->set('request', $request, 'request');
        $em = $contenedor->get('doctrine')->getManager();

        $scheduler = $contenedor->get('acme_scheduler.scheduler');
        $myJob = $scheduler->createJob('acme_backend_conexion');
        $myJob
            ->setName('Conexiones Compuestas')
            ->setRepeatEvery('+1 month')
//            ->setScheduledIn('+10 second')  
            ->setScheduledAt((new \DateTime('now')))
            ->program();

        $scheduler->schedule($myJob);

    }
}
