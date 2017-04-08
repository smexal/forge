<?php

namespace Forge\Core\App;

use \Forge\Core\Traits\Singleton;

class ModifyHandler {
    use Singleton;

    private $modifiers = [];

    public function add($name, $callback, $priority=10, $args=1) {
        if (!array_key_exists($name, $this->modifiers)) {
            $this->modifiers[$name] = [];
        }

        $modifier = ['priority' => $priority, 'callback' => $callback, 'args' => $args];
        array_push($this->modifiers[$name], $modifier);
    }

    public function remove($name, $callback, $priority) {
        if (array_key_exists($this->modifiers, $name)) {
            foreach ($this->modifiers as $id => $modifier) {
                if ($callback == $modifier['callback'] && $priority == $modifier['priority']) {
                    unset($this->modifiers[$name][$id]);
                    break;
                }
            }
        }
    }

    public function trigger($name) {
        if (array_key_exists($name, $this->modifiers)) {
            foreach($this->modifiers[$name] as $key => $row) {
                $priority[$key] = $row['priority'];
            }

            asort($priority);

            $arg_list = func_get_args();
            // remove modifier name
            array_shift($arg_list);

            foreach($priority as $sorted_key => $priority) {
                $arg_list[0] = call_user_func_array($this->modifiers[$name][$sorted_key]['callback'], $arg_list);
            }
                
            return $arg_list[0];
        }
        if(func_num_args() > 1)
            return func_get_arg(1);
    }
}

