<?php

namespace Acme\BackendBundle\Command;

use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Acme\BackendBundle\Entity\Job;
use Acme\BackendBundle\Services\UtilService;

class CustomRunCommand extends ContainerAwareCommand{
    
    protected $sleepTime = 60; //1 minutos
            
    protected function configure()
    {
        $this
            ->setName('SISTEMA:scheduler:execute')
            ->setHelp("Execute remaining jobs")
        ;
    }
    
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Output.CustomRunCommand -- init");
        $contenedor = $this->getContainer();
        $contenedor->enterScope('request');
        $server = array('REMOTE_ADDR' => "127.0.0.1");
        $request =  new Request();
        $request->initialize(array(), array(), array(), array(), array(), $server);
        $request->setMethod("None");
        $request->setSession(new Session(new MockArraySessionStorage()));
        $contenedor->set('request', $request, 'request');
        $em = $contenedor->get('doctrine')->getManager();
        
        $logger = $contenedor->get("logger");   
        $logger->warn("Logger.CustomRunCommand -- init");
        
        $mailer = $contenedor->get('mailer');
        if(!$mailer->getTransport()->isStarted()){
            $mailer->getTransport()->start();
        }
    
        $emailsAdmin = $contenedor->get("doctrine")->getRepository('BackendBundle:User')->findEmailSuperAdmin();
        if(count($emailsAdmin) != 0){
            $now = new \DateTime();
            $subject = $now->format('Y-m-d H:i:s') . ". Inicio una instancia nueva de ejecución de procesos."; 
            UtilService::sendEmail($contenedor, $subject, $emailsAdmin, "");
        }
        
        /*******************************************************************************************
         *                          JOBS RUNNING
         *******************************************************************************************/
        $jobsRunning = $contenedor->get("doctrine")->getRepository('BackendBundle:Job')->getRunningJobs();
        if(count($jobsRunning) !== 0){
            $logger->warn("Existen " . count($jobsRunning) . " procesos corriendo de la ejecucion anterior, se cambiaran a waiting.");
            $output->writeln("Existen " . count($jobsRunning) . " procesos corriendo de la ejecucion anterior, se cambiaran a waiting.");
            $em->getConnection()->beginTransaction(); // suspend auto-commit
            try {
                foreach ($jobsRunning as $job) {
                    $job->setStatus(Job::STATUS_WAITING);
                    $em->persist($job);
                }
                $em->flush();
                $em->getConnection()->commit();
                $logger->warn("Se cambio el estado satisfactoriamente.");
                $output->writeln("Se cambio el estado satisfactoriamente.");
            } catch (\Exception $e) {
                $em->getConnection()->rollback();
                $em->close();
                $logger->warn("Ocurrio una exception restableciendo el proceso.");
                $output->writeln("Ocurrio una exception restableciendo el proceso .");
                throw $e;
            }
        }else{
            $logger->warn("No existen procesos corriendo de la ejecucion anterior.");
            $output->writeln("No existen procesos corriendo de la ejecucion anterior.");
        }    
        
        /*******************************************************************************************
         *                          JOBS FAILED
         *******************************************************************************************/
        $jobsFailed = $contenedor->get("doctrine")->getRepository('BackendBundle:Job')->getFailedJobs();
        if(count($jobsFailed) !== 0){
            $logger->warn("Existen " . count($jobsFailed) . " procesos que fallaron de la ejecucion anterior.");
            $output->writeln("Existen " . count($jobsFailed) . " procesos que fallaron de la ejecucion anterior.");
            if(count($emailsAdmin) != 0){
                $now = new \DateTime();
                $subject = $now->format('Y-m-d H:i:s') . ". Existen " . count($jobsFailed) . " procesos que fallaron en la ejecución anterior y requieren atención inmediata."; 
                $body = "Procesos que fallaron:\n";
                foreach ($jobsFailed as $job) {
                     $body .= "Nombre: " . $job->getName() . "\n";
                     $body .= "Exception: " . $job->getLastExceptionToString() . "\n";
                }
                UtilService::sendEmail($contenedor, $subject, $emailsAdmin, $body);
            }
            
        }else{
            $logger->warn("No existen procesos que hayan fallado de la ejecucion anterior.");
            $output->writeln("No existen procesos que hayan fallado de la ejecucion anterior.");
        }
        
        $mailer->getTransport()->stop();
         /*******************************************************************************************
         *                         EJECUTION
         *******************************************************************************************/
        try {
            
            while (true) {
                if(!$mailer->getTransport()->isStarted()){
                    $mailer->getTransport()->start();
                }
                $logger->warn("Logger.while---init");
                $output->writeln("Output.while---init");
                $this->getContainer()->get('acme_scheduler.scheduler')
                    ->setOutputInterface($output)
                    ->executeJobs()
                ;
                $logger->warn("Logger.Esperando: " . $this->sleepTime . " segundos.");
                $output->writeln("Output.Esperando: " . $this->sleepTime . " segundos.");
                $mailer->getTransport()->stop();
                sleep($this->sleepTime);
            }
            
        } catch (\RuntimeException $ex) {
            var_dump($ex->getMessage());
            $now = new \DateTime();
            $subject = $now->format('Y-m-d H:i:s') . ". Ocurrio un error en la ejecución de procesos."; 
            $body = "Error: " . $ex->getMessage() . ". \nTrace:\n" . $ex->getTraceAsString();
            UtilService::sendEmail($contenedor, $subject, $emailsAdmin, $body);
        } catch (\Exception $ex) {
            var_dump($ex->getMessage());
            $now = new \DateTime();
            $subject = $now->format('Y-m-d H:i:s') . ". Ocurrio un error en la ejecución de procesos."; 
            $body = "Error: " . $ex->getMessage() . ". \nTrace:\n" . $ex->getTraceAsString();
            UtilService::sendEmail($contenedor, $subject, $emailsAdmin, $body);
        }
        
        $logger->warn("Logger.CustomRunCommand -- end");
        $output->writeln("Output.CustomRunCommand -- end");
    }
    
    
}
