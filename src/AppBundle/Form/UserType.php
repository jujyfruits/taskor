<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $user = $builder->getData();
        

        $builder
                ->add('username')
                ->add('email');
        
        $builder->add('roles', 'choice', array(
            'label' => 'Роли',
            'choices' => $this->roles,
            'data' => $user->getRoles(),
            'expanded' => true,
            'multiple' => true,
            'mapped' => true,
        ));


        ;
    }

    function __construct($roles) {
        $this->roles = $roles;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'appbundle_user';
    }

}
