<?php

if (count($view['todos']) == 0) {
    return "";
}
$moduleUrl = json_encode($view->getModuleUrl("/Dashboard/SystemStatus"));

echo "<div class='dashboard-item' id='dashboard-item-admintodo'>";
echo $view->header()->setAttribute('template',$T('admintodo_title'));

foreach($view['todos'] as $todo) {
    echo "<div class='Action'  id='dashboard_admintodo_{$todo['name']}' title='{$todo['name']}' data='{$todo['description']}'>";
    echo $view->form()->setAttribute('action', $todo['name'])
        ->insert($view->literal("<span'>".$T($todo['name']. '_label')."</span>"))
        ->insert($view->hidden('key')->setAttribute('value',$todo['name']))
        ->insert($view->button('X', $view::BUTTON_SUBMIT));
    echo "</div>";
}

echo "</div>";

$view->includeJavascript("
(function ( $ ) {
    function hideAll(id) {
        $(id).hide();
        if ( $('#Dashboard_SystemStatus_AdminTodo div.Action').size() <= 0 ||
             $('#Dashboard_SystemStatus_AdminTodo div.Action:visible').size() <= 0 
        ) {
            $('#Dashboard_SystemStatus_AdminTodo').hide();
        }
        $('#Dashboard_SystemStatus').masonry();
    }

    $('#Dashboard_SystemStatus_AdminTodo').on('nethguiclosetodo', function (e, arg) {
        hideAll('#dashboard_admintodo_'+arg);
    });

   $('#Dashboard_SystemStatus_AdminTodo div.Action').qtip({
       content: {
           attr: 'data',
           title: function() { return $(this).attr('title'); }
       },
       position: {
           target: 'mouse',
       },
       style: {
           widget: true, // Use the jQuery UI widget classes
           def: false // Remove the default styling (usually a good idea, see below)
       }
    });
} ( jQuery ));
"); 

$view->includeCSS("
   #dashboard-item-admintodo {
       border-color: #d33f2b;
   }
   #dashboard-item-admintodo .Action {
       padding: 5px;
       line-height: 1.2em;
       border-bottom: 1px solid #eee;
       cursor: help;
   }
   #dashboard-item-admintodo .Action:hover{
       background-color: #eee;
   }
   #dashboard-item-admintodo .ui-button-text-only .ui-button-text {
       padding: 2px;
   }
   #dashboard-item-admintodo .Button.submit {
       font-weight: bold;
       background: none;
       color: #d33f2b;
       border: none;
       border-bottom: 1px solid #eee;
       padding: 0px;
       margin: -1px;
       float: right;
   }
   #dashboard-item-admintodo h2 {
       background-color: #d33f2b;
       color: #fff;
   }
");
