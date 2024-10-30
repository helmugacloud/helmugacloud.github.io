cd /var/www/vhosts/helmuga.cloud/tracker.cyclingcloud.com/githubpages/helmugacloud.github.io
tippecanoe --force --read-parallel --no-feature-limit --no-tile-size-limit --coalesce --simplify-only-low-zooms --minimum-zoom=6 --maximum-zoom=15 --no-tile-compression --include=color --output-to-directory=/var/www/vhosts/helmuga.cloud/tracker.cyclingcloud.com/githubpages/helmugacloud.github.io/2251/tiles /var/www/vhosts/helmuga.cloud/tracker.cyclingcloud.com/replay-map/2251/*.json
git add --all
git commit -m "Initial commit"
git push -u origin main