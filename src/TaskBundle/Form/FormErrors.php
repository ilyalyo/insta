<?php
namespace TaskBundle\Form;
class FormErrors
{
    public function getArray(\Symfony\Component\Form\Form $form)
    {
        return $this->getErrors($form);
    }

    private function getErrors($form)
    {
        $errors = array();

        if ($form instanceof \Symfony\Component\Form\Form) {

            // соберем ошибки элемента
            foreach ($form->getErrors() as $error) {

                $errors[] = $error->getMessage();
            }

            // пробежимся под дочерним элементам
            foreach ($form->all() as $key => $child) {
                /** @var $child \Symfony\Component\Form\Form */
                if ($err = $this->getErrors($child)) {
                    $errors[$key] = $err;
                }
            }
        }

        return $errors;
    }
}