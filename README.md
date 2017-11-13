# FORGE
...yo

## Modules
...

### Proposed Folder Structure
The following module structure is proposed but not mandatory for a module. We try to keep 'em all the same for recognition.
The Forge core provides fully automatic autoloading for modules which are built in the default strcture. Check the Autoload and Autoregister Sections for more informations  
```
<module-name>/
|___.noautoload       (Optional. See Autoload-Section)
|___autoregister.json (Optional. See Autoregister-Section)
|___assets/
|   |___css/
|   |___images/
|   |___scripts/
|___classes/
|   |___externals/
|___components/
|___collections/
|___templates/
|   |___cache/
|___views/
|___module.php
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

## Autoloading
Forge provides fully automatic autoloading of PHP-classes. Forge does this automatically inside the whole forge directory.
In order for Forge to do this correctly, your class files have to:
* Define a namespace
* Have only one class / interface / trait declaration inside it


The autoloading process caches your files. Thus you have to flush the cache when changing your config or adding a TYPE. 
You can do this by setting the constant `AUTOLOADER_CLASS_FLUSH` to true or setting the GET-Parameter `flushac`

### Disable Autoloading
You can disable the autoloading in your theme / module by adding a .noautoload file inside your root directory. 
The .noautoload file can also be placed inside a subdirectory in order to avoid autoloading specific files. E.g. Libraries which are used by your module. 

## Autoregistration
Forge provides the possibility to automatically register standard forge components like collections, views or components. In order for your module / theme to enable the auto registration, you have to add a autoregister.json File in your module root directory.

The autoregistration process caches your files. Thus you have to flush the cache when changing your config or adding a TYPE. 
You can do this by setting the constant `MANAGER_CACHE_FLUSH` to true or setting the GET-Parameter `flushmc`.


## autoregister.json
Following are the possible configuration-properties for the autoregister Files
The TYPE: is either views, components or collections
```
{
 "namespace": "\\My\\Namespace", // (required) The base namespace for the module with no trailing slash
 "nsfromtype": true,             // (optional) Adds the matching TYPE as a sub-package E.g: \My\Namespace\TYPE
 "disabled": [TYPE1, TYPE2],     // (optional) List of the types which shall not be autoregistered
 "TYPE": {                       // (optional) Add a Type-Array to make specific configurations
     "folder": "foldername",     // (optional) Defines a custom folder instad of the default (TYPE)
     "package": "SPECIALNS"      // (optional) Defines a custom sub-package for the TYPE. E.G: \My\Namespace\SPECIALNS
 } 
}
```

# Collections, Metas, Relations

## Collections
TBD

### Fields
Collections can define fields which are then represented in the frontend of the Adminview

#### Repeater
Example:
```php
$repeater_field [
'key' => 'myrepeater',
    'label' => \i('Maaai Repeater', 'forge-tournaments'),
    'multilang' => false,
    'type' => 'repeater',
    'order' => 10,
    'position' => 'left',
    'hint' => i('Select the participant status', 'forge-tournaments'),
    'subfields' => [
         [
            'key' => 'alpha',
            'label' => i('Alpha', 'forge-tournaments'),
            'value' => "val_alpha",
            'multilang' => false,
            'type' => 'text',
            'hint' => i('Short key', 'forge-tournaments')
        ],
        [
            'key' => 'url',
            'label' => i('Website', 'forge-tournaments'),
            'value' => "",
            'multilang' => true,
            'type' => 'url',
            'order' => 60,
            'position' => 'right',
            'hint' => i('Link to the website', 'forge-tournaments')
        ],
        [
            'key' => 'image_logo',
            'label' => i('Logo', 'forge-tournaments'),
            'value' => "",
            'multilang' => true,
            'type' => 'image',
            'order' => 70,
            'position' => 'right',
            'hint' => i('Logo', 'forge-tournaments')
        ],
        [
            'key' => 'qc_action', 
            'label' => \i('Action', 'forge-quests'),
            'values' => [
                'alpha' => 'Alpha',
                'beta' => 'Beta',
                'gamma' => 'Gamma',
                'delta' => 'Delta',
                'yotta' => 'Yotta'
            ],
            'value' => 'gamma',
            'multilang' => false,
            'type' => 'select',
            'order' => 20,
            'position' => 'left'
        ],
        [
            'key' => 'comments',
            'label' => i('Allow Comments (Disqus)', 'forge-news'),
            'multilang' => true,
            'type' => 'checkbox',
            'order' => 20,
            'position' => 'right',
            'hint' => ''
        ],
         [
            'key' => 'end-date',
            'label' => i('End Date', 'forge-events'),
            'multilang' => true,
            'type' => 'datetime',
            'order' => 30,
            'position' => 'right',
            'hint' => ''
        ],
        [
            'key' => 'price',
            'label' => i('Event Price', 'forge-events'),
            'multilang' => true,
            'type' => 'number',
            'order' => 19,
            'position' => 'right',
            'hint' => ''
        ]
    ]
];
             
```
## Metas
TBD

## Relations

### Registering new relations in the Relation Directory
Example at: https://github.com/smexal/forge-tournaments/blob/master/module.php#L65
            https://github.com/smexal/forge-tournaments/blob/master/collections/collection.phase.php#L42
```php            
 \registerModifier('Forge/Core/RelationDirectory/collectRelations', 
    'my_new_relations');
function my_new_relations($existing) {
    return array_merge($existing, [
        'forge_teams-team_teammember' => new CollectionRelation(
            // This is the name in the db-column "name"
            'forge_teams-team_teammember',
            TeamCollection::COLLECTION_NAME, 
            TeamMemberCollection::COLLECTION_NAME, 
            RelationDirection::DIRECTED
        )
    ]);
}
```

### Retrieving new relations
Example at: https://github.com/smexal/forge/blob/master/core/classes/class.fieldloader.php#L32
```php
$relation = $field['relation'];
$relation = App::instance()->rd->getRelation($relation['identifier']);
// The special case of Direction::REVERSED is not yet implemented here
$list_of_ids = $relation->getOfLeft($item->id, Prepares::AS_IDS_RIGHT);

// Directly generates CollectionItems iff the Relation registered is a CollectionRelation
$list_of_collections = $relation->getOfLeft($item->id, Prepares::AS_INSTANCE_RIGHT);
```

### Saving or Adding relations
Example at: https://github.com/smexal/forge/blob/master/core/classes/class.fieldsaver.php#L32
```php
$relation = $field['relation'];
$rel = App::instance()->rd->getRelation($relation['identifier']);
// Maxes a diff from the items in the DB then removes the missing won in $right_item_ids and adds the new one
$rel->setRightItems($item->id, $right_item_ids);
// There is no interface for setting via an object, always an ID
$rel->add($r_item->id, $r_item->id);
$rel->addMultiple($r_item->id, [42, 1337, 80085]);
```

# Migrations
Modules often need to be updated. Forge provides a migration interface which checks different migration steps and orders them correctly based on the provided versions.
For this to work you have to place you migraion in the mirations folder as followed:
```
modules/forge-tournament
|_migrations
  |_migration.forgetournaments_0_0_1.php
```
## Example Class
Not the Namespace, Classname and interface which is used. Make shure that your migration class defines a unique identifier. This, because the consecutive versions are identified by the identifier and targetversion.
```
<?php
namespace Forge\Modules\ForgeTournaments;

use Forge\Core\Traits\Singleton;
use Forge\Core\Interfaces\IMigration;

use Forge\Core\App\App;

class Forgetournaments_0_0_1Migration implements IMigration {
    use Singleton;
    
    public static function identifier() {
        return 'forge-torunaments';
    }

    public static function targetversion() {
        return '0.0.1';
    }

    public static function oninstall() {
        return true;
    }

    public static function prepare() {

    }

    public static function execute() {
        try {
            App::instance()->db->startTransaction();
            App::instance()->db->query(
                'CREATE TABLE `ft_datastorage` (
                    `ref_type` VARCHAR(32) NOT NULL,
                    `ref_id` INT(11) NOT NULL,
                    `source` VARCHAR(16) NOT NULL,
                    `group` VARCHAR(16) NOT NULL,
                    `key` VARCHAR(16) NOT NULL,
                    `value` VARCHAR(64),
                    `changed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`ref_type`, `ref_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
            );
            App::instance()->db->commit();
        } catch (Exception $e) {
            App::instance()->db->rollback();
        }

    }
}
```

