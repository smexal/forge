<?php

namespace Forge\Core\Classes;

use \Forge\Core\App\App;

class AdminDashboard {
    use \Forge\Core\Traits\Singleton;

    private $widgets = [];

    public function registerWidget($id, $args) {
        if(!array_key_exists('width', $args)) {
            $args['width'] = 'normal';
        }
        if(!array_key_exists('height', $args)) {
            $args['height'] = 'normal';
        }
        if(!array_key_exists('title', $args)) {
            $args['title'] = $id;
        }
        if(!array_key_exists('callable', $args)) {
            $args['callable'] = [$this, 'noWidgetContent'];
        }

        $this->widgets[] = [
            'id' => $id,
            'title' => $args['title'],
            'callable' => $args['callable'],
            'width' => $args['width'],
            'height'=> $args['height']
        ];
    }

    public function render() {
        return App::instance()->render(CORE_ROOT."ressources/templates/components", "dashboard", [
            'widgets' => $this->renderWidgets()
        ]);
    }

    public function renderWidgets() {
        $wellWidgets = [];
        foreach ($this->widgets as $widget) {
            $wellWidgets[] = [
                'id' => $widget['id'],
                'title' => $widget['title'],
                'content' => $this->widgetContent($widget['callable']),
                'width' => 'width-'.$widget['width'],
                'height' => 'height-'.$widget['height']
            ];
        }
        return $wellWidgets;
    }

    private function widgetContent($callable) {
        if(is_array($callable)) {
            $method = $callable[1];
            return $callable[0]->$method();
        } else {
            return call_user_func($callable);
        }
    }

    public function noWidgetContent() {
        return i('No Widget content defined', 'core');
    }

}

?>
