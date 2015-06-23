<?php
namespace AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class SupportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('message', 'textarea', array('label' => 'Сообщение', 'attr' => array('placeholder'=>'Введите Ваше сообщение..')))
            ->add('isDuplicateToEmail', 'checkbox', array('label' => 'Дублировать на почту' , 'required' => false ));
    }

    public function getName()
    {
        return 'admin_support';
    }
}