#!/bin/bash
cd /var/www/vhosts/helmuga.cloud/tracker.cyclingcloud.com/githubpages/helmugacloud.github.io
/usr/local/bin/tippecanoe --force --read-parallel --no-feature-limit --no-tile-size-limit --coalesce --simplify-only-low-zooms --minimum-zoom=6 --maximum-zoom=15 --no-tile-compression --include=color --output-to-directory=/var/www/vhosts/helmuga.cloud/tracker.cyclingcloud.com/githubpages/helmugacloud.github.io/2251/tiles /var/www/vhosts/helmuga.cloud/tracker.cyclingcloud.com/replay-map/2251/*.json
/usr/bin/git add --all
/usr/bin/git commit -m "Initial commit"
/usr/bin/git push -u origin main