## Basic release process (Cheatsheet)

1. Go into DEV branch
2. Do work
3. add, commit work and push
4.  run `$ git flow release start <version>`
5. Do any remaining work
6. run `$ git flow release finish <version>`
7. run `$ git push origin --tags`

**Remaining tasks on Github:**
 - merge dev with master; create PR
 - goto 'releases' > [https://github.com/xandermar/drupal-composer-drush-redis/releases](https://github.com/xandermar/drupal-composer-drush-redis/releases)
 - click 'Draft new release'
 - select 'release' number and title
 - save

> Once the 'release' is updated, it will trigger Docker Hub to deploy a
> new image build for the release number.

