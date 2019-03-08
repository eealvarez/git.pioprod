<?php

namespace Acme\MayaBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

class EstacionToNumberTransformer implements DataTransformerInterface{

    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function transform($object)
    {
        if (null === $object) {
            return "";
        }

        return $object->getId();
    }

    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }

        $object = $this->om
            ->getRepository('MayaBundle:Estacion')
            ->findOneBy(array('id' => $id))
        ;

        if (null === $object) {
            throw new TransformationFailedException(sprintf(
                'La estación con identificador "%s" no existe!',
                $id
            ));
        }

        return $object;
    }
    
}
