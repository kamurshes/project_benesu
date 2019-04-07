#!/usr/bin/env bash
# スクリプトのディレクトリを取得する
SCRIPT_DIR=$(cd $(dirname $0);pwd)
. ${SCRIPT_DIR}/benesu.conf
curl -X POST -H "Authorization: Bearer ${Bearer}" -F "message=

おはようございます。

朝目覚めて動き出す前に水、

300～500ccを飲みましょう！

" https://notify-api.line.me/api/notify
