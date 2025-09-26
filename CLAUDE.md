# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Environment

This is a modern Drupal 11.2 project set up for Acquia hosting that uses Docksal for local development. The project follows Kanopi Studios' standards and includes comprehensive automated testing, deployment pipelines, and theming based on the Kanopi Design Component Library (KDCL).

### Local Development Setup
- Uses Docksal (docksal.io) for containerized local development
- Requires Acquia API credentials: `fin config set --global SECRET_ACQUIA_CLI_KEY=XXX` and `fin config set --global SECRET_ACQUIA_CLI_SECRET=XXX`
- Initial setup: `git clone` → `cd PROJECT` → `fin init`

## Key Commands

### Docksal Commands (prefix with `fin`)
- `init` - Initialize project from scratch
- `refresh` - Get database and files from Acquia using ACLIv2
- `composer [command]` - Run Composer commands in CLI container
- `npm [command]` - Run npm commands for theme development
- `share` - Open ngrok proxy for external access
- `pa11y` - Run accessibility audits

### Composer Scripts (run with `fin composer`)

#### Code Quality & Analysis
- `code-check` - Run all static analysis (PHPStan, Rector, PHPCS)
- `code-sniff` - PHPCS on custom modules and themes
- `code-sniff-ci` - PHPCS with JUnit XML output for CI
- `code-fix` - Auto-fix code style issues with PHPCBF and Rector
- `phpstan` - Static analysis on custom code
- `phpstan-ci` - PHPStan with JUnit XML output for CI
- `rector-check` - Combined rector modules and themes (dry run)
- `rector-modules` / `rector-themes` - Check for deprecations (dry run)
- `rector-check-ci` - Rector with JUnit XML output for CI
- `rector-fix` - Auto-fix deprecations
- `lint-php` - Basic PHP syntax checking

#### Twig Template Quality
- `twig-lint` - Lint Twig templates in custom modules and themes
- `twig-lint-ci` - Twig linting with JUnit XML output for CI
- `twig-fix` - Auto-fix Twig template formatting issues

### Theme Development (run with `fin npm run` in theme directory)
Theme is located at `docroot/themes/custom/THEME` and uses Storybook + Webpack:
- `develop` - Watch mode for Storybook and Webpack
- `storybook` - Start Storybook development server
- `build` - Build production Storybook
- `webpack` - Run Webpack build

## Project Architecture

### Directory Structure
- `docroot/` - Drupal web root (Acquia standard)
  - `modules/custom/` - Custom Drupal modules
  - `themes/custom/THEME/` - Custom theme based on KDCL
  - `sites/` - Drupal multisite configuration
- `config/` - Drupal configuration exports
- `recipes/` - Drupal recipes for reusable functionality
- `scripts/` - Deployment and utility scripts
- `hooks/` - Acquia Cloud Hooks for deployment automation
- `tests/cypress/` - Cypress end-to-end tests
- `.docksal/` - Docksal configuration and custom commands

### Code Quality Tools
- **PHPStan** (level 5): Static analysis with CI reporting (PHPStan ^2.0)
- **PHPCS/PHPCBF**: Drupal coding standards with JUnit XML reporting
- **Rector**: Automated refactoring for deprecations (Rector ^0.21.0)
- **Twig CS Fixer**: Template formatting and linting (configured in `.twig-cs-fixer.php`)
- **Composer Normalize**: Automated composer.json formatting
- **Lefthook**: Git hooks (currently disabled in `lefthook.yml`)

### CI/CD Pipeline
- **CircleCI**: Separate workflows for each tool (PHPStan, PHPCS, Rector, Twig lint)
- **Enhanced Testing**: Lighthouse performance, Pa11y accessibility, BackstopJS visual regression
- **Tugboat**: Creates preview environments for PRs with comprehensive testing
- **Acquia Deployment**: Main branch auto-deploys to dev environment
- **Cloud Hooks**: Automated config imports and database updates on Acquia deployments

### Testing Strategy
- Cypress tests in `tests/cypress/` for end-to-end testing
- Pa11y accessibility audits via Docksal command and CI
- BackstopJS visual regression testing (configured in `backstop.json`)
- Lighthouse performance audits in CI pipeline
- PHPUnit configured but minimal test suite

## Important Notes

### Modern Drupal Features
- Uses `docroot/` directory structure (Acquia hosting standard)
- Drupal 11.2 with latest security updates
- Support for Drupal recipes via `drupal/core-recipe-unpack`
- Default content module for easy content seeding

### Theme Development
- Theme uses Emulsify DS/KDCL architecture with Storybook
- Webpack config requires `watchOptions` for Docksal containers
- Storybook available at `http://storybook.${VIRTUAL_HOST}` locally
- Production Storybook deployed to GitHub Pages

### Acquia Integration
- Uses Acquia Solr (default Solr 7) with local Docksal override
- Memcache integration via `acquia/memcache-settings`
- Drush aliases and settings.php files provided by Acquia

### Development Workflow
1. Create feature branch and work locally with Docksal
2. Push to GitHub - CircleCI runs separate workflows for each quality tool
3. Tugboat creates preview environment with comprehensive testing (Lighthouse, Pa11y, BackstopJS)
4. Merge to main triggers deployment to Acquia dev environment
5. Use Acquia UI for stage/prod deployments

### CI/CD Best Practices
- Always use the `-ci` composer scripts for CI environments (they output JUnit XML)
- Each quality tool runs in its own CircleCI workflow for better parallelization
- All test results are stored as artifacts with proper reporting
- Failed builds post detailed reports to GitHub PRs

When running linting/testing commands, always use the Composer scripts rather than calling tools directly to ensure proper configuration and paths are used.