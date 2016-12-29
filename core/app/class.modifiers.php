<?php

namespace Forge\Core\App;

use Forge\Core\Traits as Traits;

class ModifyHandler {
    use Traits\Singleton;

    private $modifiers = [];

    public function add($name, $callback, $priority=10, $args=1) {
        if (!array_key_exists($name, $this->modifiers)) {
            $this->modifiers[$name] = [];
        }

        $modifier = ['prio' => $priority, 'callback' => $callback, 'args' => $args];
        array_push($this->modifiers[$name], $modifier);
    }

    public function remove($name, $callback, $priority) {
        if (array_key_exists($this->modifiers, $name)) {
            foreach ($this->modifiers as $id => $modifier) {
                if ($callback == $modifier['callback'] && $priority == $modifier['prio']) {
                    unset($this->modifiers[$name][$id]);
                    break;
                }
            }
        }
    }

    public function trigger($name) {
        if (array_key_exists($name, $this->modifiers)) {
            foreach ($this->modifiers[$name] as $key => $row) {
                $priority[$key] = $row['priority'];
            }

            array_multisort($volume, SORT_ASC, $this->modifiers[$name]);

            $arg_list = func_get_args();
            // remove modifier name
            array_shift($arg_list);

            call_user_func_array($this->modifiers[$name]['callback'], $arg_list);
        }
    }
}

?>