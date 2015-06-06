<?php
namespace TaskBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class FollowByIdType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('count', 'text',array('label' => 'Количество', 'attr' => array('placeholder'=>'100')))
            ->add('tags', 'text',array('label' => 'ID'))
            ->add('speed', 'choice', array(
                'choices' => array(
                    '0'   => '20-30 с',
                    '1' => '30-45 с',
                    '2'   => '1-1.5 мин',
                ),
                'label' => 'Скорость',
                'multiple' => false,
            ));
    }

    public function getName()
    {
        return 'followbyid';
    }
}