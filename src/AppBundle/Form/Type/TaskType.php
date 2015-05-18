<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Choice;

class TaskType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('name', 'text');
        $builder->add('description', 'textarea');
        $builder->add('estimated_time', 'integer');
        $builder->add('sprint', 'choice', array(
          'choices' => $this->sprints
        ));

        if (!empty($this->parent_task)) {
            $builder->add('parent', 'entity', array(
                'label' => 'Parent task',
                'class' => 'AppBundle:Task',
                'choices' => $this->parent_task,
                'property' => 'name'
            ));
        } else {
            $builder->add('parent', 'entity', array(
                'label' => 'Parent task',
                'class' => 'AppBundle:Task',
                'choices' => $this->task_arr,
                'property' => 'name',
                'placeholder' => 'Choose parent task',
                'required' => false,
                'empty_data' => null
            ));
        }

        $builder->add('save', 'submit', array('attr' => array('class' => 'button simple-button'), 'label' => 'Создать задачу'));
        $builder->add('saveAndAdd', 'submit', array('attr' => array('class' => 'button finish-button'), 'label' => 'Создать и добавить подзадачу'));
    }

    function __construct($parent_task, $task_arr, $project) {
        $this->parent_task = $parent_task;
        $this->task_arr = $task_arr;

        $interval = date_diff((new \DateTime()), $project->getCreatedAt())->days;
        $current_sprint = floor($interval/7);
        $this->sprints = array();

        for($i=0; $i<10; $this->sprints[]=$current_sprint+$i, $i++);
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
