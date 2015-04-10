<?php

namespace ZfrRestTest\Asset\InputFilter;

use Doctrine\ORM\EntityManagerInterface;
use DoctrineModule\Validator\NoObjectExists;
use DoctrineModule\Validator\UniqueObject;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Identical;
use Zend\Validator\ValidatorChain;

class SimpleInputFilter extends InputFilter
{

    public function __construct() {
        $this->add(
            [
                'name'       => 'fields1',
                'required'   => true,
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                    ]
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'fields2',
                'required'   => true,
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                    ]
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'fields3',
                'required'   => true,
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                    ]
                ],
            ]
        );
    }
} 