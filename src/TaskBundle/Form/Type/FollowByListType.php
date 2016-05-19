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
                    '0'   => '30-60 с',
                    '1' => '1-1.5 мин',
                    '2'   => '1.5-2 мин',
                ),
                'label' => 'Скорость',
                'multiple' => false,
            ))
            ->add('optionAddLike', 'checkbox', array('label' => 'Ставить лайк перед подиской' , 'required' => false ))
            ->add('optionFollowClosed', 'checkbox', array('label' => 'Подписываться на закрытые страницы' , 'required' => false ))
            ->add('optionCheckUserFromDB', 'checkbox', array('label' => 'Подписываться на бывших подписчиков' , 'required' => false ))
            ->add('optionFollowersFrom', 'integer', array('label' => 'от' , 'required' => false, 'render_optional_text' => false  ))
            ->add('optionFollowersTo', 'integer', array('label' => 'до' , 'required' => false, 'render_optional_text' => false  ))
            ->add('optionFollowFrom', 'integer', array('label' => 'от' , 'required' => false, 'render_optional_text' => false  ))
            ->add('optionFollowTo', 'integer', array('label' => 'до' , 'required' => false, 'render_optional_text' => false  ))
            ->add('optionHasAvatar', 'checkbox', array('label' => 'Фоловить только аккаунты с аватаром' , 'required' => false, 'render_optional_text' => false  ))
            ->add('optionStopPhrases', 'textarea', array('label' => 'Стоп слова в био', 'required' => false,
                'attr' => array('placeholder'=>'магазин,продажа,путешествия'),
                'render_optional_text' => false ))
            ->add('optionGeo', 'hidden',array('required' => false));
    }

    public function getName()
    {
        return 'followbylist';
    }
}