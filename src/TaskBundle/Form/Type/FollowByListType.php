<?php
namespace TaskBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class FollowByListType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tmp_tags', 'textarea', array(
                'label' => 'Cписок с ID',
                'attr' => array('placeholder'=>
                    'instastellar
how_in_eanglish
dima_bilan')
            ))
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
        return 'followbylist';
    }
}