# FORGE
...yo

## Modules
...

### Proposed Folder Structure
The following module structure is proposed but not mandatory for a module. We try to keep 'em all the same for recognition.
```
<module-name>/
|   assets/
    |   css/
    |   images/
    |   scripts/
|   classes/
    |   components/
    |   collections/
    |   externals/
    |   views/
|   templates/
    |   cache/
|   module.php
```

### Example module.php
```php
<?php

namespace Forge\Modules\<YourNamespace>;

use \Forge\Core\Abstracts\Module as AbstractModule;



class Module extends AbstractModule {

    public function setup() {
        $this->version = '1.0.0';
        $this->id = "forge-module-name";
        $this->name = i('Module name', 'textdomain');
        $this->description = i('Describe your module.', 'textdomain');
        $this->image = $this->url().'assets/images/module-image.png';
    }

    public function start() {
    }

}

?>
```