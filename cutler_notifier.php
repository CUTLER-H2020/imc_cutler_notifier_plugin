<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.plugin.plugin');
require_once JPATH_COMPONENT_SITE . '/helpers/imc.php';

class plgImccutler_notifier extends JPlugin
{


	public function onAfterNewIssueAdded($model, $validData, $id = null)
	{
		$app = JFactory::getApplication();
		$details = $this->getDetails($id, $model);
		$showMsgsBackend  = ($this->params->get('messagesbackend') && $app->isAdmin());

		if ($this->params->get('mailnewissue')){
			if($showMsgsBackend) {
			
			}

			//send data to CUTLER

		}

	}	

	public function onAfterCategoryModified($model, $validData, $id = null)
	{
		$app = JFactory::getApplication();
		$details = $this->getDetails($id, $model);
		$showMsgsBackend  = ($this->params->get('messagesbackend') && $app->isAdmin());

		if ($this->params->get('mailcategorychange')){
			if($showMsgsBackend) {
			
			}

			//send data to CUTLER

		}

	}	

	public function onAfterStepModified($model, $validData, $id = null)
	{
		$app = JFactory::getApplication();
		$details = $this->getDetails($id, $model);
		$showMsgsBackend  = ($this->params->get('messagesbackend') && $app->isAdmin());

		if ($this->params->get('mailstatuschange')) {		
			if($showMsgsBackend) {
			
			}

			//send data to CUTLER
			
		}
	}

	public function onAfterNewCommentAdded($model, $validData, $id = null)
	{
		$app = JFactory::getApplication();
		$details = $this->getDetails($id, $model);
		$showMsgsBackend  = ($this->params->get('messagesbackend') && $app->isAdmin());

		if ($this->params->get('mailnewcomment')){
			if($showMsgsBackend) {
			
			}

			//send data to CUTLER

		}
	}

	public function onAfterModerationModified($model, $validData, $id = null)
	{
		$app = JFactory::getApplication();
		$details = $this->getDetails($id, $model);
		$showMsgsBackend  = ($this->params->get('messagesbackend') && $app->isAdmin());

		//Prepare email for admins
		if ($this->params->get('cutlermoderationchange')) {
			if($showMsgsBackend) {
				$app->enqueueMessage("CUTLER plugin is triggered because moderation is changed");
			}

			//send data to CUTLER
			$dummy = array(
				'username' => 'MyUsername',
				'password' => 'MyPassword'
			);	
			$jsonData = json_encode($dummy);

			$result = $this->sendData( $jsonData );
		}

	}

	private function sendData( $jsonData )
	{
		$app = JFactory::getApplication();
		$url = $this->params->get('cutler_url');
		
		try {
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
			$output = curl_exec($ch);
			curl_close($ch);

			$result = json_decode($output);
			
			if(empty($result) || is_null($result)){
				$app->enqueueMessage("CUTLER: Empty or no JSON result", 'error');
			}
			if(isset($result->error)){
				$app->enqueueMessage("CUTLER: Result returned error code: ". $result->error, 'error');
			}
			
			$result = json_decode($output);
		}
		catch(Exception $e)	{
			$app->enqueueMessage("CUTLER: Exception occured: ". $e, 'error');
			$result = null;
		}
		return $result;

	}

	private function getDetails($id, $model) 
	{
		$issueid = $id;
		//check if issue added from frontend
		if($id == null){
			$issueid = $model->getItem()->get('id');
		} 

		require_once JPATH_COMPONENT_ADMINISTRATOR . '/models/issue.php';
		//JModelLegacy::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/models');
		$issueModel = JModelLegacy::getInstance( 'Issue', 'ImcModel' );

		$item = $issueModel->getItem($issueid);
		$userid = $item->get('created_by');

		$details = new stdClass();
		$details->issueid = $issueid;
		$details->userid = $userid;

		return $details;
	}
}
