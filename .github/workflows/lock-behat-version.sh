#!/bin/bash

cat <<< $(jq --indent 4 --arg version "$VERSION" '.require |= with_entries(if (.key == "behat/behat") then .value=$version else . end)' < composer.json) > composer.json
