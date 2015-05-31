<?php

namespace AppBundle\Twig;

class AppExtension extends \Twig_Extension {

    public function getFilters() {
        return array(
            new \Twig_SimpleFilter('price', array($this, 'priceFilter')),
            new \Twig_SimpleFilter('subtaskrus', array($this, 'subTaskRusFilter')),
            new \Twig_SimpleFilter('checkchildrenstate', array($this, 'checkChildrenStateFilter')),
            new \Twig_SimpleFilter('logstaterus', array($this, 'logStateRusFilter')),
        );
    }

    public function priceFilter($number, $decimals = 0, $decPoint = '.', $thousandsSep = ',') {
        $price = number_format($number, $decimals, $decPoint, $thousandsSep);
        $price = '$' . $price;

        return $price;
    }

    public function subTaskRusFilter($number) {
        if (($number % 10 == 1) and ( $number % 100 != 11)) {
            $string = 'подзадача';
        } elseif (($number % 10 >= 2) and ( $number % 10 <= 4) and ( ($number % 100 < 10) or ( $number % 100 >= 20))) {
            $string = 'подзадачи';
        } else {
            $string = 'подзадач';
        }
        return $string;
    }

    public function checkChildrenStateFilter($task) {
        foreach ($task->getChildren() as $child) {
            if ($child->getState() != 'Finished') {
                return false;
            }
        }
        return true;
    }

    public function logStateRusFilter($state) {

        switch ($state) {
            case 'Created':
                $string = 'создал задачу';
                break;
            case 'Edited':
                $string = 'изменил задачу';
                break;
            case 'Started':
                $string = 'начал выполнение задачи';
                break;
            case 'Finished':
                $string = 'закончил задачу';
                break;
            case 'Unstarted':
                $string = 'отказался от задачи';
                break;
        }
        return $string;
    }

    public function getName() {
        return 'app_extension';
    }

}
