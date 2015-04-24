<?php 

class FourOhFour extends AbstractView {
    public $name = '404';

    public function content() {
        return '<h1>Four Oh! Four<h1>';
    }
}