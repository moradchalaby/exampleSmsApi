<?php
foreach(glob(__DIR__.'/Helpers/*.php') as $helperFile){
    require_once $helperFile;
}
