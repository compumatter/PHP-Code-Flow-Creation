function cm_code_flow_record($location,$note='',$vars='') {
    global $record_code_flow;
    # should be set in cm-alpha.php 
    if($record_code_flow != 'on'){
        return;
    }
    # we will often want to clear the $GLOBALS when we are trying to track down a new code flow from a specific point
    if($note=='clear_globals'){
        unset($GLOBALS['code_flow']);
        $note='';
    }
    if(strpos($location,".php") !== false) {
        $file_or_function='file';
    } else {
        $file_or_function='function';
    }
 
    if($file_or_function=="file"){
        # error_log("CODE FLOW FILE IS $location");
        $included_by='';      
        $backtrace = debug_backtrace();
        if (isset($backtrace[1]['file'])) {
            if($included_by != $location){
                $backtraceFile=str_replace(PLUGINS_DIR,"",$backtrace[1]['file']);
                if(strlen($vars) == ''){
                    $class="file_section";
                    $thisfile="$backtraceFile";
                    $line_info="<br><span>".str_replace(PLUGINS_DIR,"",$location)."</span><br><span class='file_included_by'> was included from : $thisfile</span>"; 
                } else {
                    $class="var_section";
                    $trace = debug_backtrace();
                    $caller = $trace[0];                    
                    /*
                    if("{$caller['line']}" == 90){
                        error_log('CALLING THE DUMP');
                        cm_var_dump($trace);
                    }
                    */
                    $line_info="Line {$caller['line']} of ".str_replace(PLUGINS_DIR,"",$location)." $vars";
                    $abbrevLocation='';
                }
            }
        }            
       $element="$line_info $note";        
    }
    if($file_or_function=="function"){
        $class="function_section";        
        $trace = debug_backtrace();
        $caller = $trace[1];
        #if("{$caller['function']}" == "kplugin_or_scode"){
        #cm_var_dump($trace);
        #}
        $callerFile=str_replace(PLUGINS_DIR,"","{$caller['file']}");
        $element="function {$caller['function']} fired from <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='function_called_from' style=''>#{$caller['line']} of $callerFile</span>";
       # if (isset($caller['class'])) {
       #     echo "in class {$caller['class']}";
       # }
    }
    $GLOBALS['code_flow'][] = "<div class='$class'>$element</div>";    
}
function cm_code_flow_view($array, $filename = 'code_flow.html',$headline='') {
    // Initialize the variable to hold all the lines
    $lines = "";
    // Iterate through the array and append each element to the $lines variable
    foreach ($array as $value) {
        $lines .= $value . "\n";
    }
    // Define the path where the file will be saved
    $filePath = '/insert/your/codeflow/dir/here/' . $filename;
    ob_start();
    ?>
    <style>
    body {
        background-color: #f0f0f0;
        font-family:arial;
    }
    .var_section {
        margin: .2em;
        color: #666666;
        font-size: 12px;
    }        
    .file_section {
        padding: 0.5em;
        color: white;
        background: black;
    }
    .file_included_by{
        margin-left:40px;
        color: #8e8a8a;
    }
    .function_section {
        padding: .5em;
        background: purple;
        color: white;
    }
    .function_called_from{
        color: #A8A6A6;
    }
    .headline {
        font-size: 22px;
        width: 100%;
        clear: both;
        float: left;
        text-align: center;
        margin-bottom: .5em;
        background: white;
    }    
    </style>
    <?php
    $css_code = ob_get_clean();
    $title="<div class='headline'>Code Flow Chart For $headline</div>";
    file_put_contents($filePath, $css_code);
    file_put_contents($filePath, $title,FILE_APPEND);
    file_put_contents($filePath, $lines,FILE_APPEND);
    // Send to the error log
    error_log($lines);
}
