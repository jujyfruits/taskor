<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

//use Symfony\Component\Validator\Constraints\Choice;

class ProjectType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('name', 'text',array('label' => 'Название'));
        $builder->add('description', 'textarea',array('label' => 'Описание'));
        $builder->add('sprint_length', 'integer',array('label' => 'Длина спринта(в днях)'));
        $builder->add('save', 'submit', array('attr' => array('class' => 'button simple-button'), 'label' => 'Сохранить'));
    }

    function __construct(array $options = array()) {
        $this->options = $options;
    }

    public function getName() {
        return 'project';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Project',
        ));
    }

}
