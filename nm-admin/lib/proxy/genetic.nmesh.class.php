<?php

class algorithm {

	private $population;
	const MAX_GENERATIONS = 50000;
	const FITNESS_THRESHOLD = 0;
  
  private $generation=0;

	public function __construct()
	{

	}
	
	public function getBestCandidates(){

			return $this->population->getBestCitizens();
	}
	
	public function setObjective($objective = NULL){

			$this->population->setObjective($objective);
	}
	
	public function startUp()
	{
			$this->population->startUp();
			$this->population->calculateFitness();
			$this->population->assignBestCitizens();
	}
	
	public function isFinished($fitness_threshold = 0){

			$bests = $this->getBestCandidates(0);
			$can0 = $bests[0]->getFitness();
			return ($can0 == $fitness_threshold);
      
	}
	
	function microtime_float()
  {
    list($useg, $seg) = explode(" ", microtime());
    return ((float)$useg + (float)$seg);
  }
	
  public function getSynWeight ($layer) {
		$weight = array();
    
    $l_count = count($layer);
    for($l = 0; $l < $l_count; ++$l) { // Now we walk through each layer of the net
			
			foreach($layer[$l]->neuron as $neuron) {

				foreach($neuron->synapse as $synapse) {

					$weight[] = $synapse->weight;
				}

				$weight[] = $neuron->bias;
			}
		}

    return $weight;
  }
  
  public function start ($outputarray,$learningrate,$layer) {

      if ($this->generation == 0) {
    		$this->population = new Population();
        $this->population->population[0]= new Citizen();
        $this->population->population[0]->setData($this->getSynWeight($layer));
        $this->population->startUp();
        $this->generation++;
      }           
  }
  
	public function sstart($bank = NULL){

			//BUCLE HASTA ENCONTRAR EL MEJOR CANDIDATO
			$end = FALSE;
			$generation = 0;
			$stime = $this->microtime_float();
			while($end == FALSE && $generation < self::MAX_GENERATIONS){
				//CREAMOS CANDIDATOS A PARTIR DE LOS DOS MEJORES CANDIDATOS DE LA GENERACIÓN ANTERIOR
				$this->population->reproduce();

				//MUTAMOS LOS CANDIDATOS DE LA GENERACIÓN ACTUAL
				$this->population->mutate();

				//EVALUAMOS LOS CANDIDATOS Y ESTABLECEMOS LOS DOS MEJORES DE ESTA GENERACIÓN
				$this->population->calculateFitness();
				$this->population->assignBestCitizens();

				//PRINTAMOS POR PANTALLA LOS DOS MEJORES CANDIDATOS DE ESTA GENERACIÓN
				$bests = $this->population->getBestCitizens();
				echo "GENERATION: $generation" . PHP_EOL."<br>";
				$i = 0;
				foreach($bests as $b){
				  ++$i;
				  echo "Best Candidate $i: " . $b->getData() . " - " . $b->getFitness() . PHP_EOL."<br>";
				}
				echo PHP_EOL . PHP_EOL;

				$generation++;
				$end = $this->isFinished(0, self::FITNESS_THRESHOLD);
			}
			$ftime = $this->microtime_float();
			echo "Time evolving: " . ($ftime - $stime) . 'sec' . PHP_EOL . PHP_EOL;
			return $this->population->getBestCitizens();

	}
}

class Population{
	
	const MAX_POPULATION = 2000;
	public $population = array();
	public $bestCitizens = array();
	
	public function __construct(){
		$this->init();
	}
	
	private function init(){
		for($x=0; $x<self::MAX_POPULATION; $x++){
			$this->population[$x] = new Citizen();
		}
		
		$this->bestCitizens[0] = new Citizen();
    $this->bestCitizens[0]->setFitness(1000);
	}
	
	public function calculateFitness(){
		for($x=0; $x<self::MAX_POPULATION; $x++){
			$this->population[$x]->calculateFitness($this->objective);
		}
	}
	
	public function startUp(){
		for($x=1; $x<self::MAX_POPULATION; $x++){
			$this->population[$x]->setData($this->randomData(count($this->population[0])));
      echo implode(" - ",$this->population[$x]->getData());
		}
	}
	
	private function randomData($length){

    $temp = array();

		for($x=0;$x<$length;$x++){
			$temp[]=(mt_rand(0,1*20000)/10000)-1;
		}
		return $temp;
	}
	
	private function cmp($a, $b){
		$a = $a->getFitness();
		$b = $b->getFitness();
		
	    if ($a == $b) {
	        return 0;
	    }
	    return ($a < $b) ? -1 : 1;
	}
	
	public function assignBestCitizens(){
		usort($this->population, array($this,"cmp"));
		
	  $this->bestCitizens[0]->setData($this->population[0]->getData());
  	$this->bestCitizens[0]->setFitness($this->population[0]->getFitness());
	}
	
	public function getBestCitizens(){
		return $this->bestCitizens;
	}
	
	public function reproduce(){
	  $best = $this->population[0]->getData();
		for($x=1; $x<self::MAX_POPULATION; $x++){
		  $temp = $this->population[$x]->getData();
      for($y=0;$y<strlen($this->objective);$y++){
        if(mt_rand(0,1)){
          $temp[$y] = $best[$y];
        }
      }
      
  		$this->population[$x]->setData($temp);
		}
	}
	
	public function mutate(){
		for($x=0; $x<self::MAX_POPULATION; $x++){
			$data = $this->population[$x]->getData();
			for($y=0;$y<strlen($this->objective);$y++){
			  if(mt_rand()%100<4){
			    $data[$y] = chr(mt_rand(32, 126));
			  }
			}
			
			$this->population[$x]->setData($data);
		}
	}
}

class Citizen {
	
	private $data;
	private $fitness;
	
	public function __construct()
	{
		$this->data = '';
		$this->fitness = -1;
	}
	
	public function getData()
	{
		return $this->data;
	}
	
	public function setData($newData = NULL)
	{
		$this->data = $newData;
	}
	
	public function getFitness()
	{
		return $this->fitness;
	}
	
	public function setFitness($newFitness = NULL)
	{
		$this->fitness = $newFitness;        
	}
	
	public function calculateFitness($objective = NULL){

			$fitness = 0;
			for($x=0;$x<strlen($objective);$x++){
				$fitness += abs(ord($objective[$x]) - ord($this->data[$x]));
			}
			
			$this->setFitness($fitness);		
	}
	
}
?>