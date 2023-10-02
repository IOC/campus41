# moodle-local_userdebug

This local plugin allows to activate and deactivate the debug mode only for the
current user. The access is restricted to administrators.

To install the plugin, download the files inside the folder local/courseoverview, go
to _Site administration | Notifications_ and follow the standard steps. Finally,
edit the file _config.php_ and add the following lines:
    
```php
require_once __DIR__ . '/local/userdebug/lib.php';
userdebug_get_debug();
```
