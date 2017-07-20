<?php
$stats_id = (isset($stats_id)?$stats_id:'dashboard_stats_'. rand(0, 1000));
$title = (isset($title)?$title:__('Details'));
$details = (isset($details)?$details:array());
$options = (isset($options)?$options:array());
$class = (isset($options['class'])?$options['class']:'dashboard-stats');
?>

<div class="<?= $class; ?>" id="<?= $stats_id ?>">
	<?php if($title): ?><h3><?= $title; ?></h3><?php endif; ?>
	<?php
		$content = '';
		$i=0;
		$pieCnt = 0;
		foreach ($details as $key => $detail)
		{
			$class = '';
			if(isset($detail['class'])) $class = $detail['class'];
			
			$div_options = array('class' => 'dashboard-stat dashboard-stat-'. $i. ' '. str_replace('.', '_', $key));
			
			if(!isset($detail['pie_exclude']) or !$detail['pie_exclude'] and $detail['value'])
			{
				$div_options['class'] .= ' pie-indexed';
				$div_options['data-pie-index'] = $pieCnt;
				$pieCnt++;
			}
			
			$title = $this->Html->tag('span', trim($detail['name'])?$detail['name']:'&nbsp;', array('class' => 'title '. $class));
			
			$color = '';
			if(isset($detail['color']))
				$color = $this->Html->tag('span', '', array('class' => 'color ', 'style' => 'background-color: #'.$detail['color'].';'));
			
			$value = '0';
			if(trim($detail['value']))
			{
				$value = $detail['value'];
				if(isset($detail['filter_data']))
				{
					$filter_data = array(
						'field' => false,
						'value' => $value,
					);
					if(isset($detail['escape']) and $detail['escape'])
					{
						$filter_data['escape'] = $detail['escape'];
					}
					if(is_string($detail['filter_data']))
					{
						$filter_data['field'] = $detail['filter_data'];
					}
					elseif(is_array($detail['filter_data']))
					{
						$filter_data = array_merge($filter_data, $detail['filter_data']);
					}
					$value = $this->Wrap->filterLink($value, $filter_data);
				}
				elseif(isset($detail['escape']) and $detail['escape'])
				{
					$value = $this->Wrap->escape($value);
				}
				elseif((int)$value)
				{
					$value = $this->Wrap->niceNumber($value);
				}
			}
			$value = $this->Html->tag('span', $value, array('class' => 'value '. $class));
			$content .= $this->Html->tag('div', $color.$title.$value, $div_options);
			$i++;
		}
		echo $content;
	?>
</div>