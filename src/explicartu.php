<?php
    //Version number
    $ver = "1.0";

    //Define colors
    $red = "\033[1;31m";
    $green = "\033[1;32m";
    $yellow = "\033[1;33m";
    $brown = "\033[0;33m";
    $blue = "\033[1;34m";
    $rc = "\033[0m";
    
    //Explicartu keywords
    $keywords = ["class", "is", "var", "function", "does", "parameter", "end-class", "returns", "todo", "end-function", "project-title", "project-description"];

    //Check call arguments
    if(count($argv) < 2){
        echo($red."Error:$rc no input files specified.\n");
        fail($blue."Usage:$rc explicartu <file to parse> <file to parse> ...\n");
    }else{
        echo($blue."Explicartu$brown $ver$rc, ready to document!\n");
    }
    
    //If arguments are right, load all files specified
    $file_contents = [];
    for($i = 1; $i < count($argv); ++$i){
        echo("- Loading ".$argv[$i]."... ");
        if(file_exists($argv[$i])){
            array_push($file_contents, file_get_contents($argv[$i]));
            echo($green."Loaded!$rc\n");
        }else{
            fail($red."Error:$rc file $blue".$argv[$i]."$rc not found.\n");
        }
    }
    
    //If all files were loaded, proceed to remove everything that's not a possible Explicartu directive.
    echo("- Obtaining comments from sources... ");
    $explicartu_lines = [];
    foreach($file_contents as $file){
        $lines = explode("\n", $file);
        //Trim all lines
        foreach($lines as $linenum => $line){
            $lines[$linenum] = trim($line);
        }
        //Parse all lines leaving only comments
        foreach($lines as $linenum => $line){
            $lines[$linenum] = "";
            //Find where the comment starts (if any)
            $hasComment = false;
            for($i = 0; $i < strlen($line)-1; ++$i){
                if($line[$i]  == "/" && $line[$i] == $line[$i+1]){
                    $line = substr($line, $i+2);
                    $hasComment = true;
                    break;
                }
            }
            //If no comments were found, splice the line and continue
            if(!$hasComment){
                continue;
            }
            //If the comment is empty, splice the line and continue
            if(strlen($line) == 0){
                continue;
            }
            //If the comment doesn't start with (, then it's not a Explicartu directive.
            //Splice and continue.
            if($line[0] != "("){
                continue;
            }
            array_push($explicartu_lines, $line);
        }
    }
    unset($file_contents);
    echo($green."Done!$rc\n");
    
    //Proceed to parse each line and remove each possible directive that's not a real one.
    echo("- Obtaining Explicartu directives... ");
    foreach($explicartu_lines as $linenum => $line){
        //Remove the first parenthesys
        $line = substr($line, 1);
        //Get what's inside the parenthesys and the rest in $parts
        $parts = explode(")", $line, 2);
        //Get the keyword
        $keyword = explode(" ", $parts[0], 2)[0];
        //If the keyword is not a valid keyword, splice the line.
        if(!in_array($keyword, $keywords)){
            array_splice($explicartu_lines, $linenum, 1);
        }
    }
    echo($green."Done!$rc\n");
    
    //Proceed to parse each and operate according to every directive.
    echo("- Parsing directives... ");
    $inClass = ""; //Current Class
    $inFunction = ""; //Current Function
    $superSection = "";
    $functionSection = "";
    $sections = [];
    $index = [];
    $project_title = "Untitled Project";
    $project_description = "";
    foreach($explicartu_lines as $linenum => $line){
        //Remove the first parenthesys
        $line = substr($line, 1);
        //Get what's inside the parenthesys and the rest in $description
        $parts = explode(")", $line, 2);
        $description = trim($parts[1]);
        //Get the keyword and parameters
        $key_pars = explode(" ", $parts[0]);
        //Operate according to the keyword
        $keyword = $key_pars[0];
        switch($keyword){
            case("var"):
                //Write var text for the documentation
                $text = "<span class='variable'>".$key_pars[1]."</span>";
                $text = $text . " (<span class='datatype'>" . $key_pars[2] . "</span>): ";
                $text = $text . "<i>" . $description . "</i>";
                //Push if standalone variable, append if part of class.
                if(strlen($inClass) == 0){
                    $superSection = $superSection . "<p><b>Global variable:</b> " . $text;
                    $superSection = "<div class='var' id='$key_pars[1]'>".$superSection."</div>";
                    array_push($index, [0, "Variable: " . $key_pars[1], $key_pars[1]]);
                    array_push($sections, [0, $superSection]);
                    $superSection = "";
                }else{
                    $superSection = $superSection . "<span class='prop'>Property:</span> " . $text . "<br>";
                }
                break;
                
            case("project-title"):
                $project_title = $description;
                break;
                
            case("project-description"):
                $project_description = $description;
                break;
                
            case("class"):
                $inClass = $description;
                $superSection = "<div class='class' id='$inClass'><h2>Class '" . $description . "'</h2>";
                break;
                
            case("is"):
            case("does"):
                if(strlen($inFunction) == 0){
                    $superSection = $superSection . "<p style='margin-top:0px;'><b>Description:</b> " . $description . "</p>";
                }else{
                    if(strlen($inClass) != 0){
                        $functionSection = $functionSection . "<b>-</b> ";
                    }
                    $functionSection = $functionSection . "<b>Description:</b> " . $description . "<br>";
                }
                break;
                
            case("todo"):
                if(strlen($inFunction) == 0){
                    $superSection = $superSection . "<p class='todo'><b>TODO:</b> <i>" . $description . "</i></p>";
                }else{
                    $functionSection = $functionSection . "<span class='todo'>";
                    if(strlen($inClass) != 0){
                        $functionSection = $functionSection . "<b>-</b> ";
                    }
                    $functionSection = $functionSection . "<b>TODO:</b> <i>" . $description . "</i></span><br>";
                }
                break;
                
            case("parameter"):
                $text = "<span class='variable'>".$key_pars[1]."</span>";
                $text = $text . " (<span class='datatype'>" . $key_pars[2] . "</span>)" . ": <i>" . $description . "</i><br>";
                if(strlen($inClass) != 0){
                        $functionSection = $functionSection . "<b>-</b> ";
                    }
                $functionSection = $functionSection . "<b>Parameter:</b> " . $text;
                break;
            
            case("returns"):
                $text = "<span class='datatype'>" . $key_pars[1] . "</span>: <i>" . $description . "</i><br>";
                $functionSection = $functionSection . "<b>Return type: </b> " . $text;
                break;
                
            case("end-class"):
                $superSection = $superSection . "</div>";
                array_push($index, [2, "Class: " . $inClass, $inClass]);
                array_push($sections, [2, $superSection]);
                $superSection = "";
                $inClass = "";
                break;
                
            case("function"):
                if(strlen($inClass) == 0){
                    $functionSection = "<h2> Global function '" . $description . "'</h2>";
                }else{
                    $functionSection = "<p><span class='met'>Method: </span>" . $description . "<br>";
                }
                $inFunction = $description;
                break;
                
            case("end-function"):
                if(strlen($inClass) == 0){
                    $functionSection = "<div class='fun' id='$inFunction'>".$functionSection."</div>";
                    array_push($index, [1, "Function: " . $inFunction, $inFunction]);
                    array_push($sections, [1, $functionSection]);
                    $functionSection = "";
                }else{
                    $superSection = $superSection . $functionSection;
                    $functionSection = "";
                }
                $inFunction = "";
                break;
        }
        
    }
    echo($green."Done!$rc\n");
    
    //Sort by type of section
    echo("- Sorting sections... ");
    sort($sections);
    echo($green."Done!$rc\n");
    
    //Create output file
    echo("- Creating output file... ");
    $output = "<title>" . $project_title . " | Explicartu</title>";
    $output = $output . "<meta charset='utf-8'>";
    $output = $output . "<style>";
    $output = $output . "html{ border-top: 8px solid grey;}";
    $output = $output . "body{ padding:0px; margin: 2em; background-color: white; max-width: 800px; margin-left:auto; margin-right:auto;}";
    $output = $output . "a{ color: #3274c9; text-decoration: none;}";
    $output = $output . "a:hover{ text-decoration: underline;}";
    $output = $output . "h2{ margin-bottom: 0px; margin-top: 0px;}";
    $output = $output . ".class{ border-left: 8px solid #FF9900; padding-left: 1em; background-color: #fff2e5; padding-top: 1em; padding-bottom: 1em;
    overflow:hidden;}";
    $output = $output . ".class h2{ color: #ff9900;}";
    $output = $output . ".var{ border-left: 8px solid #298e53; padding-left: 1em; background-color: #eff9f4;
    overflow:hidden;}";
    $output = $output . ".fun{ border-left: 8px solid #b71919; padding-left: 1em; background-color: #f2eaea; padding-top: 1em;
    overflow:hidden; padding-bottom: 1em;}";
    $output = $output . ".index{ padding-left: 1em; padding-top: 2em; overflow:hidden; padding-bottom: 1em; }";
    $output = $output . ".fun h2{ color: #b71919;}";
    $output = $output . ".prop{ color: #298e53; font-weight: 700;}";
    $output = $output . ".met{ color: #b71919; font-weight: 700;}";
    $output = $output . ".todo{ background-color: #dddd80;}";
    $output = $output . ".datatype{ font-family:monospace; color:blue;}";
    $output = $output . ".variable{ font-family:monospace; color:#234f03; font-weight:700; font-size: 1.1em;}";
    $output = $output . "</style>";
    $output = $output . "<center><h1>".$project_title."</h1></center>";
    if($project_description != ""){
        $output = $output . "<div style='text-align:center; max-width:500px; margin:auto; font-size:0.9em; color:#555555; margin-top:0.5em;'><i>".$project_description."</i></div>";
    }
    $output = $output . "<div class='index'><h2 id='index'>Index</h2><ul style='margin-top:0px'>";
    sort($index);
    foreach($index as $topic){
        $output = $output . "<li><a href='#$topic[2]'>".$topic[1]."</a></li>";
    }
    $output = $output . "</ul></div>";
    foreach($sections as $section){
        $output = $output . $section[1];
        $output = $output . "<div style='text-align:right; margin-bottom:10px; margin-top:10px;'>[<a href='#index'>Back</a>]</div>";
    }
    echo($green."Done!$rc\n");
    
    //Save output file
    echo("- Saving output... ");
    file_put_contents("explicartu.html", $output);
    echo($green."Done!$rc\n");
    die("Compilation finished.\n");
    
    //Fail function
    function fail($text){
        echo($text);
        die("Compilation failed.\n");
    }
?>
