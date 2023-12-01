# Craft Akamai Invalidator

Assign cache tags to pages and invalidate them on save.

## Features

-   Assigns a cache tag based on the entry ID to each response.
-   Automatically invalidates the cache tag of an entry on save.
-   Invalidate the whole website via the `all` cache tag.

## Requirements

This plugin requires Craft CMS 4.4.7.1 or later, and PHP 8.1 or later.

## Installation

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require fork/craft-akamai-invalidator

# tell Craft to install the plugin
./craft plugin/install akamai-invalidator
```

## Configuration

### Akamai authentication

To generate your Akamai credentials, see [Create authentication credentials](https://techdocs.akamai.com/developer/docs/set-up-authentication-credentials).

Place the `.edgerc` file at your Craft root.

### Plugin configuration

To configure the plugin, create a file `config/akamai-invalidator.php` with the following options:

```php
<?php

return [
    'invalidateOnSave' => true,
    'enableInvalidateAll' => false,
    'network' => 'staging',
    'edgeRcSection' => 'default',
    'edgeRcPath' => '@root/.edgerc',
];
```

#### Configuration options

-   `invalidateOnSave` — Whether the cache automatically gets invalidated on entry save
-   `enableInvalidateAll` — Whether all pages can be invalidated at once via a Craft cache clear option.
-   `network` — The Akamai network in which the invalidate takes place. Either `staging` or `production`.
-   `edgeRcSection` — The credentials section within `.edgerc`
-   `edgeRcPath` — The path to the `.edgerc` file. May use [Craft Aliases](https://craftcms.com/docs/4.x/config/#aliases).

---

<div align="center">
  <a href="https://www.fork.de"><img src="./assets/heart.png" width="38" height="41" alt="Fork Logo" /></a>
  <p>Brought to you by <a href="https://www.fork.de">Fork Unstable Media GmbH</a></p>
</div>
