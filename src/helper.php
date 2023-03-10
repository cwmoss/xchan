<?php

namespace xchan;

function dbg($txt, ...$vars) {
    // im servermodus wird der zeitstempel automatisch gesetzt
    //	$log = [date('Y-m-d H:i:s')];
    $log = [];
    if (!is_string($txt)) {
        array_unshift($vars, $txt);
    } else {
        $log[] = $txt;
    }
    $log[] = join(' ~ ', array_map(fn ($v) => json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $vars));

    error_log(join(' ', $log));
}


function template($name, $data, $context = []) {
    $fname = "{$context['base']}/{$name}.html";
    $layout = "";
    extract($data);
    ob_start();
    include($fname);
    $html = ob_get_clean();
    if ($layout) {
        $html = template(
            $layout,
            $data,
            array_merge($context, ['from' => $name, 'content' => $html])
        );
    }
    return $html;
}
