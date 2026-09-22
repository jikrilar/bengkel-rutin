#!/bin/sh
set -eu

if [ ! -x node_modules/.bin/vite ]; then
    npm install --no-audit --no-fund
fi

exec npm run dev -- --host 0.0.0.0 --port 5173
