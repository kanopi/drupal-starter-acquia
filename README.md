# [Drupal Acquia]()

Basic setup for Drupal on aquia.


## Project init notes

Why? Non-pantheon projects are signifigantly different in terms of all the pieces it's easier to start from here.

1. Create new repo from template
    * Setup a github team specific to the project
    * Add branch protection
    * Squash and merge only
1. Get the acquia settings.php file and drush alias file for site in acquia
1. Update docksal/circleci environment variables as necessary
1. Make docksal is setup with KDCL basic based theme.   see docksal command.
1. Add code@kanopi to acquia project
1. Add tugboat ssh key to code@kanopi acquia account
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

## Local development

We use [Docksal](https://docksal.io/) to do our local development. Please [install Docksal](https://docksal.io/installation) first.

### Acquia Config

Configuring Acquia a key and secret must be set up to download asset from Acquia like the database. To create a key and secret go to the
[API Tokens](https://cloud.acquia.com/a/profile/tokens).

```bash
fin config set --global SECRET_ACQUIA_CLI_KEY=XXX
fin config set --global SECRET_ACQUIA_CLI_SECRET=XXX
```

Once your key has been added, verify that you have access to the project in Acquia, then
proceed with the following steps to build the project:

```shell
git clone git@github.com:kanopi/REPO.git
cd REPO
fin init
```

## Theme

The theme is based off the [Kanopi Design Component Library](https://github.com/kanopi/kdcl_basic) which uses Storybook and is originally forked from [Emulsify DS](https://github.com/emulsify-ds/emulsify-drupal).

The theme included is located within `docroot/sites/themes/custom/THEME`.

The Storybook Design System published to the `gh-pages` branch and is available
at https://kanopi.github.io/REPO_NAME/

Locally, running `fin npm run storybook` or `fin npm run build` will build the
Storybook at http://storybook.${VIRTUAL_HOST}

The theme uses Webpack and NPM to manage packages and run scripts.

- @TODO [Hot Reloading in Drupal](https://docs.emulsify.info/usage/hot-reload-drupal)

#### Storybook

- Theme development run `fin npm run develop`. This will watch for all twig, js, and sass changes within the components directory.
- The development storybook URL http://storybook.${VIRTUAL_HOST}

#### Storybook Webpack
For webpack storybook to work within a docksal container we needed to set `watchOptions` in `docroot/themes/custom/THEME/.storybook/webpack.config.js`
```
config.watchOptions = {
  aggregateTimeout: 200,
  poll: 1000,
}
```

### Theme Commands

The following commands are available with Node and should be prefixed with the
command `fin npm run`.

Command | Description
--------|------------
`commit`| git-cz
`lint`| Lint JS
`a11y`| Run a11y on theme
`storybook`| Start storybook
`build-storybook`| Build static storybook
`deploy-storybook`| Generate storybook for github pages
`webpack`| Run webpack
`build`| Build storybook
`develop`| Run storybook and webpack at the same time
`test`| Run tests
`twatch`| Watch tests run
`coverage`| Check test coverage
`format`| Clean up code format
`lint-staged`| lint-staged
`postinstall`| Patches packages
`criticalcss`| Compile critical CSS assets


## Composer Commands

The following commands are available with Composer and should be prefixed with
the command `fin composer`.

These are primarily run in CI tests, and in Lefthook pre-push githooks, but you
can still run them on your own if you wish.

Command | Description
--------|------------
`lint-php` | Analyzes the custom modules folder for programmatic and stylistic errors
`code-sniff-modules` | Runs PHPcs on the custom modules folder
`code-sniff-themes` | Runs PHPcs on the custom themes folder
`code-sniff` | Runs `code-sniff-modules` and `code-sniff-themes`
`code-fix-modules` | Runs PHPcbf on the custom modules folder
`code-fix-themes` | Runs PHPcbf on the custom themes folder
`code-fix` | Runs `code-fix-modules` `code-fix-themes` `rector-fix` `lint-php`
`phpstan` | PHPStan focuses on finding errors in the custom modules and themes folders without actually running it.
`rector-modules` | Dry run on the custom modules folder of automates that checks for deprecations
`rector-themes` | Dry run on the custom themes folder of automates that checks for deprecations
`rector-fix-modules` | Automates the refactoring of deprecations on the custom modules folder
`rector-fix-themes` | Automates the refactoring of deprecations on the custom themes folder
`rector-fix` | Runs `rector-fix-modules` and `rector-fix-themes`
`code-check` | Runs `phpstan` `rector-modules` `rector-themes` `code-sniff`

## Docksal Commands

The following commands are available with Docksal and should be prefixed with
the command `fin`

Command | Description
--------|------------
`composer` | Composer wrapper that executes within the CLI container.
`init` | Init Command that starts the project from scratch.
`init-site` | Called from `init`.
`install-theme-tools` | Runs Emulsify/Storybook setup scripts.
`npm` | NPM wrapper.
`refresh` | Will get the database and files from Acquia with ACLIv2
`share` | Opens a proxy server to your local computer using ngrok.io. Share in real time, or test locally.
`pa11y` | Runs accessbility audits against the site

### Pa11y Audits

We have a Docksal command that will run [pa11y-ci](https://github.com/pa11y/pa11y-ci) audits `fin pa11y`. When the command finishes the reports are available at the following url pa11y.VIRTUAL_HOST

If you want to change the configuration of the Pa11y tests you can edit the [.pa11yci.js](/tests/pa11y/.pa11yci.js) file.

Note: This was cribbed from [Phase2](https://github.com/phase2/pa11y-dashboard)

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

