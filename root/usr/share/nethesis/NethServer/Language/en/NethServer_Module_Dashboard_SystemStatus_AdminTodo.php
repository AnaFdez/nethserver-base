<?php

$L['admintodo_title'] = 'System checklist';
$L['X_label'] = 'X';

/* Include all language files inside the AdminTodo directory */

foreach (glob(__DIR__."/AdminTodo/*.php") as $filename)
{
    include $filename;
}
