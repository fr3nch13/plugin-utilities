<?php
/**
 * File: /app/View/Elements/stats.ctp
 * 
 * Use: provides a standard for displaying stats about an object.
 *
 * Usage: echo $this->element('stats', array([details]));
 */

$title = (isset($title)?$title:__('Stats'));
$class = (isset($options['class'])?$options['class']:'');
$sep = (isset($sep)?$sep:str_repeat('-', 30));
?>
    
<?php echo $title; ?>
    
<?php echo $sep; ?>
    
<?php foreach ($stats as $stat): ?>
    
<?php echo (isset($stat['name'])?$stat['name']:''); ?>: <?php echo ($stat['value']?$stat['value']:0); ?>
    
<?php endforeach; ?>