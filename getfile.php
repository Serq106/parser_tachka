<?php

$f = file_get_contents("https://cdn.alta-karter.ru/manuals/auto-hak/AUTO-HAK-2020-2024.pdf");

file_put_contents("zalupa.pdf", $f);