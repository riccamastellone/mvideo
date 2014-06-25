<?php

class TestController extends BaseController {
    
    /**
     * Creiamo un nuovo test e esplodiamo la lista dei signoli test
     * @return String
     */
    public function create() {
      
        $json = Input::all();
        
        $test = new Test();
        $test->media = $json['media'];
        $test->network = $json['network'];
        
        if($json['signal_strenght_steps'])
            $test->signal_strenght_steps = $json['signal_strenght_steps'];
        
        if($json['volume_steps'])
            $test->volume_steps = $json['volume_steps'];
        
        if($json['brightness'])
            $test->brightness_steps = $json['brightness'];
        $test->save();
        
        $test->explodeTest();
        return array('result' => 'success', 'message' => '');
    }
    
    /**
     * Ritorniamo il test corrente se presente, uno nuovo altrimenti.
     * Quando viene preso un nuovo test, viene anche settata la potenza del wifi,
     * causando un discreto delay nella risposta in quando deve stabilire una connessione
     * SSH al router. Sarebbe da utilizzare un queue manager per gestire questa cosa
     * in maniera asincrona ( o usare exec() )
     * @return TestElement
     */
    public function get() {
        if(!$this->currentTest()) {
	    $test = TestElement::queue()->orderBy('created_at','desc');
	    if(!$test->count()) {
		return array('status'=> 'error', 'message' => 'No test available');
	    } else {
		$test = $test->first();
	    }
	    self::setWifi($test->signal_strenght);
            return $test;
        } else {
            return $this->currentTest();
        }
        
    }
    
    /**
     * Segna come iniziato un test
     * @param int $id
     * @return TestElement
     */
    public function start($id = null) {
        if(!$id) return;
        
        // Non vogliamo avere più di un test avviato
        if($this->currentTest() && $this->currentTest()->id != $id) {
            return array('status'=> 'error', 'message' => 'You already started an other test!');
        }

        $test = TestElement::find($id);
        $test->started = date("Y-m-d H:i:s");
        $test->save();
        return $test;
    }
    
    /**
     * Segnamo come completato un test
     */
    public function complete() {
        
    }
    
    /**
     * Ritorniamo il current test se presente, false altrimenti
     * @return boolean|TestElement
     */
    private function currentTest() {
        $query = TestElement::whereNotNull('started')->where('completed', NULL)->orderBy('created_at','desc');
        if($query->count()) {
            return $query->first();
        } else {
            return false;
        }
    }
    
}
