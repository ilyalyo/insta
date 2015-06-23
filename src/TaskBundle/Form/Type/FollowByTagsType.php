<?php
namespace TaskBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class FollowByTagsType extends AbstractType
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
                    '0'   => '20-30с',
                    '1' => '30-45с',
                    '2'   => '1м-1.5м',
                ),
                'label' => 'Скорость',
                'multiple' => false,
            ))
            ->add('optionFollowClosed', 'checkbox', array('label' => 'Подписываться на закрытые страницы' , 'required' => false ))
            ->add('optionCheckUserFromDB', 'checkbox', array('label' => 'Подписываться на бывших подписчиков' , 'required' => false ));
    }

    public function getName()
    {
        return 'followbytags';
    }
}