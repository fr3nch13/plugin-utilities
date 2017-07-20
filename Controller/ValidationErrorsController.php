<?php

App::uses('UtilitiesAppController', 'Utilities.Controller');

class ValidationErrorsController extends UtilitiesAppController 
{
	public function admin_index() 
	{
		$this->Prg->commonProcess();
		
		$conditions = array();
		
		$this->ValidationError->recursive = 0;
		$this->paginate['order'] = array('ValidationError.created' => 'desc');
		$this->paginate['conditions'] = $this->ValidationError->conditions($conditions, $this->passedArgs); 
		
		$validation_errors = $this->paginate();
		$this->set('validation_errors', $validation_errors);
	}
	
	public function admin_view($id = null) 
	{
		$this->ValidationError->id = $id;
		if (!$this->ValidationError->exists()) 
		{
			return $this->redirect(array('action' => 'index'));
		}
		
		$this->ValidationError->recursive = 0;
		$this->set('validation_error', $this->ValidationError->read(null, $id));
	}

	public function admin_delete($id = null) 
	{
		$this->ValidationError->id = $id;
		if (!$this->ValidationError->exists()) {
			throw new NotFoundException(__('Invalid %s', __('Validation Error')));
		}
		if ($this->ValidationError->delete()) {
			$this->Session->setFlash(__('%s deleted', __('Validation Error')));
			return $this->redirect(array('action' => 'mine'));
		}
		$this->Session->setFlash(__('%s was not deleted', __('Validation Error')));
		return $this->redirect(array('action' => 'index'));
	}
}