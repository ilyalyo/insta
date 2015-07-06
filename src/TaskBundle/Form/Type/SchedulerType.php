<?php
namespace TaskBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class SchedulerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('runAt', 'time')
            ->add('days', 'choice', array(
                'choices' => array(
                    '1'   => '1',
                    '2'   => '1',
                    '3'   => '1',
                    '4'   => '1',
                    '5'   => '1',
                    '6'   => '1',
                    '7'   => '1',
                ),
                'label' => 'Дни',
                'multiple' => true,
            ));
    }

    public function getName()
    {
        return 'scheduler';
    }
}