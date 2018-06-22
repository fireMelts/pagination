<?php

namespace application\lib;

class Pagination {

    // $max - кол-во кнопок в пагинации
    private $max = 10;
    private $route;
    private $index = '';
    private $current_page;
    // $total - общее кол-во элементов
    private $total;
    // $limit - кол-во элементов на одной странице
    private $limit;

    public function __construct($route, $total, $limit = 10) {
        $this->route = $route;
        $this->total = $total;
        $this->limit = $limit;

        // amount() считает кол-во страниц, разделяя общее кол-во страниц на лимит
        $this->amount = $this->amount();

        //если параметра страницы нет, текущая страница будет равна 1
        //если параметр задан, текущая страница будет равна значению этого параметра
        $this->setCurrentPage();
    }

    //возвращает html код пагинации
    public function get() {
        $links = null;

        //функция возвращает стартовое и конечное положение страниц в пагинации
        $limits = $this->limits();

        //генерация html кода для пагинации
        $html = '<nav><ul class="pagination">';
        for ($page = $limits[0]; $page <= $limits[1]; $page++) {
            if ($page == $this->current_page) {
                //выделяем активную страницу
                $links .= '<li class="page-item active"><span class="page-link">'.$page.'</span></li>';
            } else {
                $links .= $this->generateHtml($page);
            }
        }
        if (!is_null($links)) {
            if ($this->current_page > 1) {
                //если мы не первой странице, добавить в начало кнопку Вперед
                $links = $this->generateHtml(1, 'Вперед').$links;
            }
            if ($this->current_page < $this->amount) {
                //если мы не на последней странице, добавить кнопку назад
                $links .= $this->generateHtml($this->amount, 'Назад');
            }
        }
        $html .= $links.' </ul></nav>';

        //возвращаем список с кнопками
        return $html;
    }

    private function generateHtml($page, $text = null) {
        if (!$text) {
            $text = $page;
        }
        //пример:             если задан второй параметр, то здесь будет его значение,
        //                                       иначе здесь будет номер страницы
        //<li class="page-item">                                   v
        //  <a class="page-link" href="/controller/action/page"> $text </a>
        //</li>
        return '<li class="page-item"><a class="page-link" href="/'.$this->route['controller'].'/'.$this->route['action'].'/'.$page.'">'.$text.'</a></li>';
    }

    private function limits() {
        // отсчитывает 5 страниц назад от текущей
        $left = $this->current_page - round($this->max / 2);
        // если $left меньше нуля, то начать отображение с первой страницы
        // если больше, то оставить как есть
        //например, текущая страница = 9, тогда $left = 4, а $start - 4
        //если же текущая страница = 3, то $left = -2, а $start - 1
        //это нужно для отображения вида 1 2 3 -4- 5 6 7 8 9 ... [max]
        $start = $left > 0 ? $left : 1;
        //  if ($left > 0) {
        //    $start = $left;
        //  } else {
        //    $start = 0;
        //  }

        //считаем последнюю страницу в пагинации
        //если $start + $max меньше чем общее кол-во страниц, то
        // end = $start + $max, иначе - $max
        //например, $start = 4, тогда $start + $max = 14 и это меньше, чем $amount(15)
        //тогда $end = 14
        //если $start = 6, то $end = 15
        if ($start + $this->max <= $this->amount) {
            $end = $start > 1 ? $start + $this->max : $this->max;
        }
        else {
            $end = $this->amount;
            $start = $this->amount - $this->max > 0 ? $this->amount - $this->max : 1;
        }

        //возвращает вычисленные значения
        return array($start, $end);
    }

    private function setCurrentPage() {
        if (isset($this->route['page'])) {
            $currentPage = $this->route['page'];
        } else {
            $currentPage = 1;
        }
        $this->current_page = $currentPage;
        if ($this->current_page > 0) {
            if ($this->current_page > $this->amount) {
                $this->current_page = $this->amount;
            }
        } else {
            $this->current_page = 1;
        }
    }

    private function amount() {
        return ceil($this->total / $this->limit);
    }
}
