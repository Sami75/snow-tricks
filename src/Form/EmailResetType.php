<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class EmailResetType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options){

    $builder
        ->add('email')
    ;

  }

  // public function configureOptions(OptionsResolver $resolver)
  // {
  //   $resolver->setDefault(array(

  //     'data_class' => null,

  //   ));
  // }
}
