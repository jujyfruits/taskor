<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TaskType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('name', 'text');
        $builder->add('description', 'textarea');
        $builder->add('estimated_time', 'integer');
        $builder->add('parent', 'entity', array(
            'label' => 'Parent task',
            'class' => 'AppBundle:Task',
            'choices' => $this->task_arr,
            'property' => 'name',
            'placeholder' => 'Choose parent task',
            'required' => false,
            'empty_data' => null
        ));
        $builder->add('save', 'submit', array('label' => 'Создать задачу'));
        $builder->add('saveAndAdd', 'submit', array('label' => 'Создать и добавить новую'));
    }
    
function __construct($task,$task_arr) {
    $this->task = $task;
    $this->task_arr = $task_arr;
}

    public function getName() {
        return 'task';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Task',
        ));
    }

}
