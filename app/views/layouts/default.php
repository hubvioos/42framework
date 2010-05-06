<?php
echo $this->loadFragment('layout-top', $this->vars);
echo $content_for_layout; 
echo $this->loadFragment('layout-bottom', $this->vars);
?>