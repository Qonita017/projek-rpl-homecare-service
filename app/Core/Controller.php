<?php
class Controller {
    public function view($view, $data = [], $layout = "main") {
        extract($data);
        $content = "../app/Views/" . $view . ".php";
        require_once "../app/Views/layouts/" . $layout . ".php";
    }

    public function model($model) {
        require_once "../app/Models/" . $model . ".php";
        return new $model();
    }
}
