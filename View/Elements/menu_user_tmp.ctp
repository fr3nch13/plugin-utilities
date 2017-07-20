<?php ?>
					<ul>
					<?php if (AuthComponent::user('id')): 
						$name = (AuthComponent::user('name')?AuthComponent::user('name'):AuthComponent::user('email'));
						if(AuthComponent::user('OrgGroup.name'))
						{
							$name = '['. AuthComponent::user('OrgGroup.name'). '] '. $name;
						}
					?>
						<li>Welcome: <?php echo $name; ?></li>
						<li><?php echo $this->Html->link(__('Edit Settings'), array('controller' => 'users', 'action' => 'edit', 'admin' => false, 'plugin' => false)); ?></li>
						<li><?php echo $this->Html->link(__('Logout'), array('controller' => 'users', 'action' => 'logout', 'admin' => false, 'plugin' => false)); ?></li>
					<?php else: ?>
						<li><?php echo $this->Html->link(__('Login'), array('controller' => 'users', 'action' => 'login', 'admin' => false, 'plugin' => false)); ?></li>
					<?php endif; ?>
					</ul>