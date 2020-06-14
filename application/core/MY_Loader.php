<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/* load the MX_Loader class */
require APPPATH."third_party/MX/Loader.php";

class MY_Loader extends MX_Loader {
	
	/**
    * Returns true if the model with the given name is loaded; false otherwise.
    *
    * @param   string  name for the model
    * @return  bool
    */
	 
	public function is_model_loaded($name) 
    {
        return in_array($name, $this->_ci_models, TRUE);
    }
}