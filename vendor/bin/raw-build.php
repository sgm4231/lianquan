#!/usr/bin/env sh

dir=$(cd "${0%[/\\]*}" > /dev/null; cd "../medz/gb-t-2260/scripts" && pwd)

if [ -d /proc/cygdrive ] && [[ $(which php) == $(readlink -n /proc/cygdrive)/* ]]; then
   # We are in Cgywin using Windows php, so the path must be translated
   dir=$(cygpath -m "$dir");
fi

"${dir}/raw-build.php" "$@"
