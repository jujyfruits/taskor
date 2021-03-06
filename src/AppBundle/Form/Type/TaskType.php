<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\Intl\DateFormatter\IntlDateFormatter;

class TaskType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('name', 'text', array('label' => 'Название'));
        $builder->add('description', 'textarea', array('label' => 'Описание'));

        if (!empty($this->parent_task)) {
            $builder->add('parent', 'entity', array(
                'label' => 'Родительская задача',
                'class' => 'AppBundle:Task',
                'choices' => $this->parent_task,
                'property' => 'name'
            ));
        } else {
            $builder->add('parent', 'entity', array(
                'label' => 'Родительская задача',
                'class' => 'AppBundle:Task',
                'choices' => $this->all_tasks,
                'property' => 'name',
                'placeholder' => 'Выберите задачу',
                'required' => false,
                'empty_data' => null
            ));
            $builder->add('sprint', 'choice', array(
                'choice_list' => new ChoiceList($this->sprints_numbers, $this->sprints_dates_text),
                'required' => false,
                'mapped' => false,
                'label' => 'Спринт',
                'placeholder' => 'Выберите спринт',
                'data' => ($this->task_sprint_number) ? $this->sprints_numbers[$this->task_sprint_number] : null
            ));
        }

        $builder->add('save', 'submit', array('attr' => array('class' => 'button simple-button'), 
            'label' => 'Сохранить'));
        $builder->add('saveAndAdd', 'submit', array('attr' => array('class' => 'button finish-button'), 
            'label' => 'Сохранить и добавить подзадачу'));
    }

    function __construct($parent_task, $all_tasks, $sprints_numbers, $sprints_dates_text, $task_sprint_number) {
        $this->parent_task = $parent_task;
        $this->all_tasks = $all_tasks;
        $this->sprints_numbers = $sprints_numbers;
        $this->sprints_dates_text = $sprints_dates_text;
        $this->task_sprint_number = $task_sprint_number;
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
