# [Drupal Acquia]()

Basic setup for Drupal on aquia.


## Project init notes

Why? Non-pantheon projects are signifigantly different in terms of all the pieces it's easier to start from here.

1. Create new repo from template
    * Setup a github team specific to the project
    * Add branch protection
    * Squash and merge only
1. Get the  drush alias file for site in acquia. can be downloaded from the UI
1. Update docksal/circleci environment variables as necessary
1. Add code@kanopistudios.com to acquia project
1. Add tugboat ssh key to code@kanopistudios.com acquia account
1. Add DDEV https://github.com/kanopi/ddev-kanopi-drupal
1. Update documentation as necessary

## Important links

* Hosting
    * [Acquia Dashboard]()
    * [Dev]()
    * [Stage]()
    * [Prod]()
* [CircleCI]()
* [Tugboat]()
* [Github Team]()



## Deployments

Deployments are managed through Tugboat, CircleCI and the Acquia UI.

Code flow:

1. Create a PR from your branch that has your updates in it.
   * CircleCI will also run code standards and rector checks against the PR.
2. This will create a Tugboat environment to test your new feature/change in.
   * Tugboat provides Lighthouse and Backstop visual regression testing.
3. Once the code is ready, merge the PR in to the default branch.
4. CircleCI will do a git push to the `dev` environment on Acquia
   * We are using [Acquia Cloud Hooks](https://docs.acquia.com/cloud-platform/develop/api/cloud-hooks/) to do a Drupal config imports and database updates on deploys in Acquia.
   * Note: If you update the major version of Drush you need to double check that version is available in Acquia.  I.E.  `drush11` or `drush10`. Currently, it's set to Drush 10.
5. To deploy to production use the Acquia UI to move the code to the different environments

## Solr

We are using Acquia Solr Search which defaults to Solr 7.

We have setup Solr on Docksal and Tugboat with environment specific config overrides in `settings.php`

```
$config['search_api.server.acquia_search_server'] = [
  'backend_config' => [
    'connector' => 'standard',
    'connector_config' => [
      'scheme' => 'http',
      'host' => 'solr',
      'path' => '',
      'core' => 'search_api_solr_8.x-2.0',
      'port' => '8983',
    ],
  ],
];
```

The Search API UI for editing the doesn't show the changes right away.  To validate the overrides run the following command `drush cget search_api.server.acquia_search_server --include-overridden`

