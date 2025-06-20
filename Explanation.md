# Explanation

Following decisins where made while developing the solution:
* Building it as WordPress plugin: it allows to leverage existing market for IT
  solutions, and WordPress ecosystem.
* Include composer.lock in Git repository (as recomended by the Composer
  project lead at https://europe.wordcamp.org/2025/session/composer-best-practices/).
* Creating custom tables: the data is very different to what WP has in Post or Options,

# TODO
Things to be fixed as a next step of the plugin development
1. Building a ZIP file that can be downloaded and intalled
   (https://github.com/marcin-wosinek/wp-link-analyzer-plugin/pull/4).
2. Fix the PHPCS warnings.
3. Fix the CRON clean up: the manual button works, but the cron sript doesn't
   remove the data.
4. Fix running the unit tests for PHP v7.
5. Investigate why plugin listing doesn't work with wp-cli on docker while
   listing crone events if fine.
6. Testing: expand the PoC unit and e2e test. Add unit tests for JS.
7. Admin page: evaluate the chart usage, plus see how then could be done
   better: with some JS library or WP plugin.
