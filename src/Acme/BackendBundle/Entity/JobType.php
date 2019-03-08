<?php
namespace Acme\BackendBundle\Entity;

class JobType
{
    const TYPE_SYNC_DEPARTAMENTO = "1.1";
    const TYPE_SYNC_ESTACION = "2.1";
    const TYPE_SYNC_TIPO_BUS = "2.2";
    const TYPE_SYNC_HORARIO_CICLICO = "2.3";
    const TYPE_SYNC_RUTA = "3.1";
    const TYPE_SYNC_TARIFA_BOLETO = "3.2";
    const TYPE_SYNC_ITINERARIO_CICLICO = "4.1";
    const TYPE_SYNC_ITINERARIO_ESPECIAL = "4.2";
    const TYPE_SYNC_TIEMPO = "4.3";
    const TYPE_SYNC_SALIDA = "5.1";
}
