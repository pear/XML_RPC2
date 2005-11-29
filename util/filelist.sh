#!/bin/bash
listdir () {
    if [ "$i" = '.' ]; then return; fi
    if [ "$i" = '..' ]; then return; fi
    if [ -d $1 ]
    then
        echo "<dir name=\"$(basename $1)\" role=\"$2\">"
        cd $1
        for i in `ls -1`
        do
            listdir $i $2
        done
        cd ..
        echo "</dir>"
    else
        echo "<file name=\"$(basename $1)\" />"
    fi
}
listdir XML php
listdir tests/XML_RPC2 test
