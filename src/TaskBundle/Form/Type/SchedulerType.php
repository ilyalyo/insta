<?php
namespace TaskBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SchedulerType extends AbstractType
{
    private $timezone;

    public function __construct($timezone) {
        $this->timezone = $timezone;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $date = new \DateTime('now', new \DateTimeZone($this->timezone));
        $builder
            ->add('runAt', 'time', array('label' => 'Время',))
            ->add('days', 'choice', array(
                'choices' => array(
                    '0'   => $date->format('d.m'),
                    '1'   => $date->add(new \DateInterval('P1D'))->format('d.m'),
                    '2'   => $date->add(new \DateInterval('P1D'))->format('d.m'),
                    '3'   => $date->add(new \DateInterval('P1D'))->format('d.m'),
                    '4'   => $date->add(new \DateInterval('P1D'))->format('d.m'),
                    '5'   => $date->add(new \DateInterval('P1D'))->format('d.m'),
                    '6'   => $date->add(new \DateInterval('P1D'))->format('d.m'),
                ),
                'label' => 'Дни',
                'multiple' => true,
                'expanded' => true,
            ));
    }

    public function getName()
    {
        return 'scheduler';
    }
}