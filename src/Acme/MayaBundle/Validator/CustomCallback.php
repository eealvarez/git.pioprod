<?php

namespace Acme\MayaBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CustomCallback extends Constraint{
   
    public $methods;

    /**
     * {@inheritDoc}
     */
    public function getRequiredOptions()
    {
        return array('methods');
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultOption()
    {
        return 'methods';
    }

    /**
     * {@inheritDoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
    
    public function validatedBy()
    {
        return 'validator.custom.callback.validator';
    }
    
}
