<?php

function initPostData(){
        $content = file_get_contents('php://input');
        $post = (array)json_decode($content, true);
        return $post;
    }

?>