<?php ?>
<li><?php echo $this->Html->link(__('Subscriptions'), ['controller' => 'subscriptions', 'action' => 'index', 'admin' => true, 'plugin' => 'utilities']); ?></li> 
<li><?php echo $this->Html->link(__('Process Times'), ['controller' => 'proctimes', 'action' => 'index', 'admin' => true, 'plugin' => 'utilities']); ?></li> 
<li><?php echo $this->Html->link(__('Process Time Queries'), ['controller' => 'proctime_queries', 'action' => 'index', 'admin' => true, 'plugin' => 'utilities']); ?></li> 
<li><?php echo $this->Html->link(__('Validation Errors'), ['controller' => 'validation_errors', 'action' => 'index', 'admin' => true, 'plugin' => 'utilities']); ?></li> 
<li><?php echo $this->Html->link(__('Version Info'), ['controller' => 'main', 'action' => 'versions', 'admin' => true, 'plugin' => 'utilities']); ?></li> 
