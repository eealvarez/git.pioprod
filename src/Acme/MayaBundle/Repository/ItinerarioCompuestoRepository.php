<?php

namespace Acme\MayaBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ItinerarioCompuestoRepository extends EntityRepository
{
     private $mapFieldToColumnsSorted = array(
        'id' => 'i.id',
        'fecha' => 'i.fecha',
        'ruta' => 'r.codigo',
        'tipoBus' => 'tb.alias',
        'activo' => 'i.activo',
        'motivo' => 'i.motivo'
    );
     
    
    
}

?>
