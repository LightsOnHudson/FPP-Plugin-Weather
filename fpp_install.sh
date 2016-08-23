#!/bin/bash
pushd $(dirname $(which $0))
target_PWD=$(readlink -f .)
exec /opt/fpp/scripts/update_plugin ${target_PWD##*/}
popd
