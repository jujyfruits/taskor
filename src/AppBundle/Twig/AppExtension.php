<?php

namespace AppBundle\Twig;

class AppExtension extends \Twig_Extension {

    public function getFilters() {
        return array(
            new \Twig_SimpleFilter('price', array($this, 'priceFilter')),
            new \Twig_SimpleFilter('subtaskrus', array($this, 'subTaskRusFilter')),
            new \Twig_SimpleFilter('checkchildrenstate', array($this, 'checkChildrenStateFilter')),
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
            if ($child->getState() != 'Finished'){
                return false;
            }
        }
        return true;
    }

    public function getName() {
        return 'app_extension';
    }

}
