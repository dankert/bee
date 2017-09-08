#!/bin/bash
#


TITLE=$1
if [ -z "$TITLE" ]; then
  TITLE=New
fi

UTC=`date --rfc-3339=seconds`
FILEPATH=site/default/`date +%Y`/`date +%m`



FILE=$FILEPATH/$TITLE.md

if [ -f $FILE ]; then
    echo "$FILE existiert bereits"
    exit 4
fi
echo "Lege an $FILE"

mkdir -p $FILEPATH
echo "---"              >> $FILE
echo "Title: $TITLE"    >> $FILE
echo "Date: $UTC"       >> $FILE
echo "Keywords: $TITLE" >> $FILE
echo "Category: $TITLE" >> $FILE
echo "Url:"             >> $FILE
echo "Author: "         >> $FILE

echo "---"              >> $FILE
echo ""                 >> $FILE
echo ""                 >> $FILE



"${EDITOR:-vi}" $FILE

echo "Thank you".