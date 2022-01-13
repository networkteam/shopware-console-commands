# Shopware console commands

Should ease plugin updates.

## Install, activate and update all plugins

```shell
./bin/console networkteam:plugin:updateall --clear-cache
```

## Plugin List with additional options

```shell
bin/console help networkteam:plugin:list

Description:
  Show a list of plugins with additional options.

Usage:
  networkteam:plugin:list [options]

Options:
      --updatable       List updatable plugins
      --installed       List installed plugins
      --uninstalled     List not installed plugins
      --active          List active plugins
      --inactive        List inactive plugins
```
