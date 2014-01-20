<?php
/**
 * NMesh object. This class is the base class.
 * 
 * Warning: A lot of effort and sanity has gone into optimizing
 * this, so some methods may not look pretty but it's all for the greater
 * good of performance. PHP is not typically recognised for it's speed,
 * nor are neural networks, so this combination was never destined to be
 * enjoyable or even possible yet here it is in all its glory.
 * 
 * Based on the PHPNN class
 * 
 * @author Louis Stowasser
 */
class algorithm {

	public function calculate_deltas($outputarray,$lr,$layer)
	{
		$mse_sum = 0;
		$l_count = count($layer)-1;
		$m = nmesh::$momentumrate;
		$error = array();
		$output_count = count($layer[$l_count]->neuron);
		
		for($l = $l_count; $l >= 0; --$l) {
			
			$error[$l] = array_fill(0,count($layer[$l]->neuron[0]->synapse),0);
			
			foreach($layer[$l]->neuron as $n=>$neuron) 
			{
				if($l===$l_count) {
					$n_error = $outputarray[$n] - $neuron->value;
					$mse_sum += $n_error * $n_error;
				} else {
          $n_error = $error[$l+1][$n];
				//echo $n;
        }
				$delta = $n_error * $this->sigmoid_derivative($neuron->value);
				
				foreach($neuron->synapse as $s=>$synapse)
				{
					$wc = $delta * $synapse->input * $lr + $synapse->momentum * $m;
					$synapse->momentum = $wc;
					$synapse->weight += $wc;
					$error[$l][$s] += $delta * $synapse->weight;
				}
				//And lets go ahead and adjust the bias too
				$biaschange = $delta * $lr + $neuron->momentum * $m;
				$neuron->momentum = $biaschange;
				$neuron->bias += $biaschange;
			}
		}
		return $mse_sum / $output_count;
	}
	
	/*
	 * Basic sigmoid derivative
	 */
	function sigmoid_derivative($value)
	{
		return $value * (1 - $value);
	}
	
}
?>