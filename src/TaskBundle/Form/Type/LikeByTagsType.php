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
            ))
            ->add('optionLastActivity', 'choice', array(
                'choices' => array(
                    '0'   => '1 день назад',
                    '1' => '1 неделя назад',
                    '2'   => '1 месяц назад',
                ),
                'label' => 'Последняя активность',
                'multiple' => false,
                'required' => false, 'render_optional_text' => false
            ))
            ->add('optionHasAvatar', 'checkbox', array('label' => 'Наличие аватара' , 'required' => false, 'render_optional_text' => false  ))
            ->add('optionSex', 'choice',  array(
                'choices' => array(
                    '0'   => 'м',
                    '1' => 'ж',
                ),
                'label' => 'Пол',
                'multiple' => false,
                'required' => false, 'render_optional_text' => false
            ))
            ->add('optionFollowClosed', 'checkbox', array('label' => 'Подписываться на закрытые страницы' , 'required' => false, 'render_optional_text' => false  ))
            ->add('optionStopPhrases', 'textarea', array('label' => 'Стоп слова в био', 'required' => false,
                'attr' => array('placeholder'=>'магазин,продажа,путешествия'),
                'render_optional_text' => false ))
            ->add('optionGeo', 'hidden',array('required' => false))

        ;
    }

    public function getName()
    {
        return 'likebytags';
    }
}