<?php
namespace TaskBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class LikeByTagsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('count', 'text', array('label' => 'Количество', 'attr' => array('placeholder'=>'100')))
            ->add('tags', 'textarea', array(
                'label' => 'Тэги',
                'attr' => array('placeholder'=>'#sun#love#peace')))
            ->add('speed', 'choice', array(
                'choices' => array(
                    '3'   => '30-50с',
                    '4' => '50с-1.15м',
                    '5'   => '1.15-1.4м',
                ),
                'label' => 'Скорость',
                'multiple' => false,
            ));
    }

    public function getName()
    {
        return 'likebytags';
    }
}