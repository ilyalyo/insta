<?php
namespace TaskBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class LikeByGeoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('count', 'text', array('label' => 'Количество', 'attr' => array('placeholder'=>'100')))
            ->add('tags', 'hidden')
            ->add('speed', 'choice', array(
                'choices' => array(
                    '0'   => '30-60 с',
                    '1' => '1-1.5 мин',
                    '2'   => '1.5-2 мин',
                ),
                'label' => 'Скорость',
                'multiple' => false,
            ))
            ->add('optionFollowersFrom', 'integer', array('label' => 'от' , 'required' => false, 'render_optional_text' => false  ))
            ->add('optionFollowersTo', 'integer', array('label' => 'до' , 'required' => false, 'render_optional_text' => false  ))
            ->add('optionFollowFrom', 'integer', array('label' => 'от' , 'required' => false, 'render_optional_text' => false  ))
            ->add('optionFollowTo', 'integer', array('label' => 'до' , 'required' => false, 'render_optional_text' => false  ))
            ->add('optionHasAvatar', 'checkbox', array('label' => 'Лайкать фото только с аккаунтов с аватаром' , 'required' => false, 'render_optional_text' => false  ))
            ->add('optionFollowClosed', 'checkbox', array('label' => 'Лайкать фото с закрытых аккаунтов' , 'required' => false, 'render_optional_text' => false  ))
            ->add('optionStopPhrases', 'textarea', array('label' => 'Стоп слова в био', 'required' => false,
                'attr' => array('placeholder'=>'магазин,продажа,путешествия'),
                'render_optional_text' => false ));
    }

    public function getName()
    {
        return 'likebygeo';
    }
}