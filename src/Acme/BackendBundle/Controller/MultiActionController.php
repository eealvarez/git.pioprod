<?php

namespace Acme\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;


/**
*   @Route(path="/admin/ajax")
*/
class MultiActionController extends Controller {

     /**
     * @Route(path="/listarItinerariosSimples", name="listarItinerariosSimples")
     */
    public function listarItinerariosSimplesAction(Request $request) {

        $options = array();
        
        try {
            $idDiaSemana = $request->query->get('idUltimoDiaSemana');
            if (is_null($idDiaSemana)) {
                $idDiaSemana = $request->request->get('idUltimoDiaSemana');
            }
            $idUltimaEstacion = $request->query->get('idUltimaEstacion');
            if (is_null($idUltimaEstacion)) {
                $idUltimaEstacion = $request->request->get('idUltimaEstacion');
            }
            
            $items = $this->getDoctrine()->getRepository('MayaBundle:ItinerarioSimple')->listarItinerariosSimples($idDiaSemana, $idUltimaEstacion);
            foreach ($items as $item) {
                
                $options[] = array(
                    "id" => $item->getId(),
                    "text" => $item->getInfo1(),
                    "clave" => $item->getClave1(),
                    "idDiaSemana" => strval($item->getDiaSemana()->getId()),
                    "codigoRuta" => $item->getRuta()->getCodigo()
                );
            }
            
        } catch (\RuntimeException $exc) {
            var_dump($exc);
        }
        catch (\Exception $exc) {
            var_dump($exc);
        }
        
        $response = new JsonResponse();
        $response->setData(array(
            'options' => $options
        ));
        return $response;
    }
    
    /**
     * @Route(path="/listarEstacionesByRuta", name="listarEstacionesByRuta")
     */
    public function listarEstacionesByRutaAction(Request $request) {

        $optionEstacionOrigen = array();
        $optionEstacionDestino = array();
        $optionsEstacionesIntermedias = array();
        
        try {
            
            $codigoRuta = $request->query->get('ruta');
            if (is_null($codigoRuta)) {
                $codigoRuta = $request->request->get('ruta');
            }
            
            $ruta = $this->getDoctrine()->getRepository('MayaBundle:Ruta')->find($codigoRuta); 
            if ($ruta !== null) {
                
                $estacionOrigen = $ruta->getEstacionOrigen();
                $estacionDestino = $ruta->getEstacionDestino();
                $listaEstacionesIntermedia = $ruta->getListaEstacionesIntermediaOrdenadas();
                
                $optionEstacionOrigen[] = array(
                    "id" => $estacionOrigen->getId(),
                    "text" => $estacionOrigen->getAliasNombre()
                );
                
                $optionEstacionDestino[] = array(
                    "id" => $estacionDestino->getId(),
                    "text" => $estacionDestino->getAliasNombre()
                );
                
                foreach ($listaEstacionesIntermedia as $item) {
                    $optionsEstacionesIntermedias[] = array(
                        "id" => $item->getEstacion()->getId(),
                        "text" => $item->getEstacion()->getAliasNombre()
                    );
                }
            }
            
        } catch (\RuntimeException $exc) {}
          catch (\Exception $exc) { }
        
        $response = new JsonResponse();
        $response->setData(array(
            'optionEstacionOrigen' => $optionEstacionOrigen,
            'optionEstacionDestino' => $optionEstacionDestino,
            'optionsEstacionesIntermedias' => $optionsEstacionesIntermedias
        ));
        return $response;
    }
}

?>
