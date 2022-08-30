#!/bin/sh
#
# Cloud Hook: drush-cache-clear
#
# Run drush cache-clear all in the target environment. This script works as
# any Cloud hook.


# Map the script inputs to convenient names.
site=$1
target_env=$2
drush_alias=$site'.'$target_env

echo "Alias: " $drush_alias

echo "Clear cache"
drush10 @$drush_alias cr

echo "Import config"
drush10 @$drush_alias cim --source=../config/sync -y

echo "Update modules"
drush10 @$drush_alias updb -y

echo "Clear cache"
drush10 @$drush_alias cr
