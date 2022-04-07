<?php

include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
 * Class for TemplateQuestion Question
 *
 * @author Yves Annanias <yves.annanias@llz.uni-halle.de>
 * @author Christoph Jobst <cjobst@wifa.uni-leipzig.de>
 * @version	$Id:  $
 * @ingroup ModulesTestQuestionPool
 */
class assPaintQuestion extends assQuestion
{
	private $plugin = null;	
	// backgroundimage	
	var $image_filename = "";
	// brushsize choosable? false - 0, true - 1
	var $lineValue = 1;
	// colourselection enabled? false - 0, true - 1
	var $colorValue = 0;	
	// canvas size for backgroundimage or individual
	var $radioOption = 'radioImageSize';
	// canvas width
	var $canvasWidth = 450;
	// canvas height
	var $canvasHeight = 400;
	// resizedImageStatus needed for backward compatibility 0 -> needs to be created; 1 -> exists
	var $resizedImageStatus = 0;
	// sample solution
	var $image_filename_bestsolution = "";
	// amount of backupimages
	var $logCount = 3;
	// enable merging the backgroundimage in addition to the logged versions
	var $logBkgr = 0;
	
	//CONFIG
	var $logCountConf = 3;
	var $logBkgrConf = 0;
	var $enableForUsersConf = 0;

	/**
	* assPaintQuestion constructor
	*
	* The constructor takes possible arguments an creates an instance of the assPaintQuestion object.
	*
	* @param string $title A title string to describe the question
	* @param string $comment A comment string to describe the question
	* @param string $author A string containing the name of the questions author
	* @param integer $owner A numerical ID to identify the owner/creator
	* @param string $question The question string of the single choice question
	* @access public
	* @see assQuestion:assQuestion()
	*/
	function __construct(
		$title = "",
		$comment = "",
		$author = "",
		$owner = -1,
		$question = ""	
	)
	{
		// needed for excel export
		$this->getPlugin()->loadLanguageModule();
		
		parent::__construct($title, $comment, $author, $owner, $question);		
	}
	
	/**
	 * @return object The plugin object
	 */
	public function getPlugin() {
		if ($this->plugin == null)
		{
			include_once "./Services/Component/classes/class.ilPlugin.php";
			$this->plugin = ilPlugin::getPluginObject(IL_COMP_MODULE, "TestQuestionPool", "qst", "assPaintQuestion");			
		}
		return $this->plugin;
	}
	
	/**
	 * Returns true, if the question is complete
	 *
	 * @return boolean True, if the question is complete for use, otherwise false
	 */
	public function isComplete()
	{
		// Please add here your own check for question completeness
		// The parent function will always return false
		if(($this->title) and ($this->author) and ($this->question) and ($this->getMaximumPoints() > 0))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	function getImageFilename()
	{
		// backgroundimage
		return $this->image_filename;
	}

	function getImageFilenameBestsolution()
	{
	    // sample solution
	    return $this->image_filename_bestsolution;
	}

	function deleteImage()
	{
		global $ilDB;
		
		$file = $this->getImagePath() . $this->getImageFilename();
		$file_resized = $this->getImagePath() ."resized_".$this->getImageFilename();
		@unlink($file); // delete image from folder
		@unlink($file_resized);
		$this->image_filename = "";
		$ilDB->manipulate('update il_qpl_qst_paint_check set '.'resized = ' . 0 .' '.'WHERE question_fi = '.$this->getId());
	}

	function deleteImageBestsolution()
	{
	    global $ilDB;
	    
	    $file = $this->getImagePath() . $this->getImageFilenameBestsolution();	    
	    @unlink($file); // delete image from folder
	    $this->image_filename_bestsolution = "";
	}

	function getLineValue()	
	{
		return $this->lineValue;
	}
	
	function getColorValue()	
	{
		return $this->colorValue;
	}
	
	function setLineValue($value)
	{		
		$this->lineValue = $value;			
	}
	
	function setColorValue($value)
	{
		$this->colorValue = $value;
	}
	
	function setRadioOption($value)
	{		
		$this->radioOption = $value;
	}
	
	function getRadioOption()
	{
		return $this->radioOption;
	}
	
	function setCanvasHeight($value)
	{
		$this->canvasHeight = $value;
	}
	
	function getCanvasHeight()
	{
		return $this->canvasHeight;
	}
	
	function setCanvasWidth($value)
	{
		$this->canvasWidth = $value;
	}
	
	function getCanvasWidth()
	{
		return $this->canvasWidth;
	}
	
	function setResizedImageStatus($value)
	{
		$this->$resizedImageStatus = $value;
	}
	
	function getResizedImageStatus()
	{
		return $this->$resizedImageStatus;
	}
	
	function setLogCount($value)
	{
	    $this->logCount = $value;
	}
	
	function getLogCount()
	{
	    return $this->logCount;
	}
	
	function setLogBkgr($value)
	{
		$this->logBkgr = $value;
	}
	
	function getLogBkgr()
	{
		return $this->logBkgr;
	}	

	function getEnableForUsersConf()
	{
	    return $this->enableForUsersConf;
	}	
	
	function getLogCountConf()
	{
	    return $this->logCountConf;
	}	
	
	function getLogBkgrConf()
	{
	    return $this->logBkgrConf;
	}	
	/**
	 * Set the image file name
	 *
	 * @param string $image_file name.
	 * @access public
	 * @see $image_filename
	 */
	function setImageFilename($image_filename, $image_tempfilename = "") 
	{		
		if (!empty($image_filename)) 
		{
			$image_filename = str_replace(" ", "_", $image_filename);
			$this->image_filename = $image_filename;
		}
		if (!empty($image_tempfilename)) 
		{
			$imagepath = $this->getImagePath();
			if (!file_exists($imagepath)) 
			{
				ilUtil::makeDirParents($imagepath);
			}
			//** TODO  hier kommt noch eine Fehlermeldung, obwohl das Bild am Ende im richtigen Ornder liegt
			
			/*if (!ilUtil::moveUploadedFile($image_tempfilename, $image_filename, $imagepath.'/'.$image_filename))
			{
				$this->ilias->raiseError("The image could not be uploaded!", $this->ilias->error_obj->MESSAGE);
			}*/
			move_uploaded_file($image_tempfilename, $imagepath.'/'.$image_filename);			
		}
	}

	/**
	 * Set the image file name
	 *
	 * @param string $image_file name.
	 * @access public
	 * @see $image_filename
	 */
	function setImageFilenameBestsolution($image_filename, $image_tempfilename = "")
	{
	    if (!empty($image_filename))
	    {
	        $microtime = round(microtime(true) * 1000);
	        $image_filename = $microtime . '.' . pathinfo($image_filename, PATHINFO_EXTENSION);
	        $this->image_filename_bestsolution = $image_filename;
	    }
	    if (!empty($image_tempfilename))
	    {
	        $imagepath = $this->getImagePath();
	        if (!file_exists($imagepath))
	        {
	            ilUtil::makeDirParents($imagepath);
	        }
	        //** TODO  hier kommt noch eine Fehlermeldung, obwohl das Bild am Ende im richtigen Ornder liegt
	        
	        /*if (!ilUtil::moveUploadedFile($image_tempfilename, $image_filename, $imagepath.'/'.$image_filename))
	         {
	         $this->ilias->raiseError("The image could not be uploaded!", $this->ilias->error_obj->MESSAGE);
	         }*/
	        move_uploaded_file($image_tempfilename, $imagepath.'/'.$image_filename);
	    }
	}

	function resizeImage($width, $height){
		
		global $ilDB;
		
		//error_log($this->getImagePath());
		//error_log($this->getImageFilename());
		
		$path = $this->getImagePath().$this->getImageFilename();
		$destination = $this->getImagePath().'resized_'.$this->getImageFilename();
		
		list ( $width, $height, $type ) = getimagesize ( $path);
		
		switch ( $type )
		{
			case 1:
				$image = imagecreatefromgif ($path);
				break;
			case 2:
				$image= imagecreatefromjpeg ($path);
				break;
			case 3:
			    $input = imagecreatefrompng ($path);
			    // Steps to convert transparency to white (instead of default black)
			    $backgroundWidth = imagesx($input);
			    $backgroundHeight = imagesy($input);
			    $image = imagecreatetruecolor($backgroundWidth, $backgroundHeight);
			    $white = imagecolorallocate($image,  255, 255, 255);
			    imagefilledrectangle($image, 0, 0, $backgroundWidth, $backgroundHeight, $white);
			    imagecopy($image, $input, 0, 0, 0, 0, $backgroundWidth, $backgroundHeight);
		}
		
		// Neue Größe berechnen
		$newwidth = $this->getCanvasWidth();
		$newheight = $this->getCanvasHeight();
		
		// Bild laden
		$resized= imagecreatetruecolor($newwidth, $newheight);
		
		// Skalieren
		imagecopyresized($resized, $image, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
		
		//Speichern
		switch ( $type )
		{
			case 1:
				imagegif($resized, $destination);
				break;
			case 2:
				imagejpeg($resized, $destination, 100);
				break;
			case 3:
				imagepng ($resized, $destination, 9);
		}
		
		$this->resizedImageStatus = 1;
		$ilDB->manipulate('update il_qpl_qst_paint_check set '.'resized = ' . 1 .' '.'WHERE question_fi = '.$this->getId());
	}
	
	
	/**
	 * Loads a question object from a database
	 * This has to be done here (assQuestion does not load the basic data)!
	 *
	 * @param integer $question_id A unique key which defines the question in the database
	 * @see assQuestion::loadFromDb()
	 */
	public function loadFromDb($question_id)
	{
		global $ilDB;
                
		// load the basic question data
		$result = $ilDB->query("SELECT qpl_questions.* FROM qpl_questions WHERE question_id = "
				. $ilDB->quote($question_id, 'integer'));

		$data = $ilDB->fetchAssoc($result);
		$this->setId($question_id);
		$this->setTitle($data["title"]);
		$this->setComment($data["description"]);
		$this->setSuggestedSolution($data["solution_hint"]);
		$this->setOriginalId($data["original_id"]);
		$this->setObjId($data["obj_fi"]);
		$this->setAuthor($data["author"]);
		$this->setOwner($data["owner"]);
		$this->setPoints($data["points"]);		

		include_once("./Services/RTE/classes/class.ilRTE.php");
		$this->setQuestion(ilRTE::_replaceMediaObjectImageSrc($data["question_text"], 1));
		$this->setEstimatedWorkingTime(substr($data["working_time"], 0, 2), substr($data["working_time"], 3, 2), substr($data["working_time"], 6, 2));			

		try {
			$this->setLifecycle(ilAssQuestionLifecycle::getInstance($data['lifecycle']));
		} catch (ilTestQuestionPoolInvalidArgumentException $e) {
			$this->setLifecycle(ilAssQuestionLifecycle::getDraftInstance());
		}

		// load backgroundImage
		$resultImage= $ilDB->queryF("SELECT image_file, image_file_sample FROM il_qpl_qst_paint_image WHERE question_fi = %s", array('integer'), array($question_id));
		if($ilDB->numRows($resultImage) == 1)
		{
			$data = $ilDB->fetchAssoc($resultImage);
			$this->image_filename = $data["image_file"];
			$this->image_filename_bestsolution = $data["image_file_sample"];
		}

		$resultCheck= $ilDB->queryF("SELECT line, color, radio_option, width, height, resized, log_count, log_bkgr FROM il_qpl_qst_paint_check WHERE question_fi = %s", array('integer'), array($question_id));
		if($ilDB->numRows($resultCheck) == 1)
		{
			$data = $ilDB->fetchAssoc($resultCheck);
			$this->lineValue = $data["line"];
			$this->colorValue = $data["color"];
			$this->setRadioOption($data["radio_option"]);
			$this->setCanvasWidth($data["width"]);
			$this->setCanvasHeight($data["height"]);
			$this->setResizedImageStatus($data["resized"]);
			$this->setLogCount($data["log_count"]);
			$this->setLogBkgr($data["log_bkgr"]);
		}
		
		// load config
		$config = $ilDB->query("SELECT enable_for_users_conf, log_count_conf, log_bkgr_conf FROM il_qpl_qst_paint_conf WHERE id = 0");
		if($ilDB->numRows($config) == 1)
		{
		    $data = $ilDB->fetchAssoc($config);
		    $this->enableForUsersConf = $data["enable_for_users_conf"];
		    $this->logCountConf = $data["log_count_conf"];
		    $this->logBkgrConf = $data["log_bkgr_conf"];
		}
		
		try
		{
			$this->setAdditionalContentEditingMode($data['add_cont_edit_mode']);
		}
		catch(ilTestQuestionPoolException $e)
		{
		}

		// loads additional stuff like suggested solutions
		parent::loadFromDb($question_id);
	}	

	/**
	* Saves a assPaintQuestion object to a database
	*
	* @access public
	*/
	function saveToDb($original_id = "")
	{
		global $ilDB, $ilLog;
		$this->saveQuestionDataToDb($original_id);			
		// save background image
		$affectedRows = $ilDB->manipulateF("DELETE FROM il_qpl_qst_paint_image WHERE question_fi = %s", 
			array("integer"),
			array($this->getId())
		);
		// save image		
		if (!empty($this->image_filename) || !empty($this->image_filename_bestsolution))
		{
			$affectedRows = $ilDB->manipulateF("INSERT INTO il_qpl_qst_paint_image (question_fi, image_file, image_file_sample) VALUES (%s, %s, %s)", 
				array("integer", "text", "text"),
				array(
					$this->getId(),
					$this->image_filename,
				    $this->image_filename_bestsolution
				)
			);
		}
		// save line and color option
		$affectedRows = $ilDB->manipulateF("DELETE FROM il_qpl_qst_paint_check WHERE question_fi = %s", 
			array("integer"),
			array($this->getId())
		);
		$affectedRows = $ilDB->manipulateF("INSERT INTO il_qpl_qst_paint_check (question_fi, line, color, radio_option, width, height, resized, log_count, log_bkgr) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)", 
				array("integer", "integer", "integer", "text", "integer", "integer", "integer", "integer", "integer"),
				array(
					$this->getId(),
					$this->getLineValue(),
					$this->getColorValue(),
					$this->getRadioOption(),
					$this->getCanvasWidth(),
					$this->getCanvasHeight(),
					$this->getResizedImageStatus(),
				    $this->getLogCount(),
					$this->getLogBkgr()
				)
		);
			
		parent::saveToDb();
	}

	/**
	* Returns the maximum points, a learner can reach answering the question
	* @access public
	* @see $points
	*/
	function getMaximumPoints()
	{		
		return $this->points;
	}

/**
* Duplicates an assPaintQuestion
*
* @access public
*/
	function duplicate($for_test = true, $title = "", $author = "", $owner = "", $testObjId = null)
	{
		if ($this->id <= 0)
		{
			// The question has not been saved. It cannot be duplicated
			return;
		}
		// duplicate the question in database
		$this_id = $this->getId();
		
		if( (int)$testObjId > 0 )
		{
			$thisObjId = $this->getObjId();
		}
		
		$clone = $this;
		include_once ("./Modules/TestQuestionPool/classes/class.assQuestion.php");
		$original_id = assQuestion::_getOriginalId($this->id);
		$clone->id = -1;
		
		if( (int)$testObjId > 0 )
		{
			$clone->setObjId($testObjId);
		}
		
		if ($title)
		{
			$clone->setTitle($title);
		}
		if ($author)
		{
			$clone->setAuthor($author);
		}
		if ($owner)
		{
			$clone->setOwner($owner);
		}
		if ($for_test)
		{
			$clone->saveToDb($original_id);
		}
		else
		{
			$clone->saveToDb();
		}

		// copy question page content
		$clone->copyPageOfQuestion($this_id);
		// copy XHTML media objects
		$clone->copyXHTMLMediaObjectsOfQuestion($this_id);
		// duplicate the image
		$clone->duplicateImage($this_id, $thisObjId);
		
		$clone->onDuplicate($thisObjId, $this_id, $clone->getObjId(), $clone->getId());
		
		return $clone->id;
	}

	/**
	* Copies an assPaintQuestion object
	*
	* @access public
	*/
	function copyObject($target_questionpool_id, $title = "")
	{
		if ($this->id <= 0)
		{
			// The question has not been saved. It cannot be duplicated
			return;
		}
		// duplicate the question in database
		$clone = $this;
		include_once ("./Modules/TestQuestionPool/classes/class.assQuestion.php");
		$original_id = assQuestion::_getOriginalId($this->id);
		$clone->id = -1;
		$source_questionpool_id = $this->getObjId();
		$clone->setObjId($target_questionpool_id);
		if ($title)
		{
			$clone->setTitle($title);
		}
		$clone->saveToDb();

		// copy question page content
		$clone->copyPageOfQuestion($original_id);
		// copy XHTML media objects
		$clone->copyXHTMLMediaObjectsOfQuestion($original_id);
		// duplicate the image
		$clone->copyImage($original_id, $source_questionpool_id);
		
		$clone->onCopy($source_questionpool_id, $original_id, $clone->getObjId(), $clone->getId());
		
		return $clone->id;
	}

	function duplicateImage($question_id, $objectId = null)
	{
		$imagepath = $this->getImagePath();
		$imagepath_original = str_replace("/$this->id/images", "/$question_id/images", $imagepath);
		
		if( (int)$objectId > 0 )
		{
			$imagepath_original = str_replace("/$this->obj_id/", "/$objectId/", $imagepath_original);
		}
		
		if (!file_exists($imagepath)) {
			ilUtil::makeDirParents($imagepath);
		}
		$filename = $this->getImageFilename();
		
		if (!empty($filename)) {
			if (!copy($imagepath_original . $filename, $imagepath . $filename)) {
				print "Image could not be duplicated.";
			}
		}
		
		if ($this->getResizedImageStatus() == 1){
			if (!copy($imagepath_original . 'resized_' .$filename, $imagepath . 'resized_' . $filename)) {
				print "Resized image could not be duplicated.";
			}
		}
		
		$filenameSampleSolution = $this->getImageFilenameBestsolution();
		
		if (!empty($filenameSampleSolution)) {
		    if (!copy($imagepath_original . $filenameSampleSolution, $imagepath . $filenameSampleSolution)) {
		        print "Image could not be duplicated.";
		    }
		}
		
	}

	function copyImage($question_id, $source_questionpool)
	{
		$imagepath = $this->getImagePath();
		$imagepath_original = str_replace("/$this->id/images", "/$question_id/images", $imagepath);
		$imagepath_original = str_replace("/$this->obj_id/", "/$source_questionpool/", $imagepath_original);
		if (!file_exists($imagepath)) 
		{
			ilUtil::makeDirParents($imagepath);
		}
		$filename = $this->getImageFilename();
		
		if (!empty($filename)) {
		    if (!copy($imagepath_original . $filename, $imagepath . $filename))
		    {
		        print "Image could not be copied.";
		    }
		}
		
		if ($this->getResizedImageStatus() == 1){
			if (!copy($imagepath_original . 'resized_' .$filename, $imagepath . 'resized_' . $filename)) {
				print "Resized image could not be duplicated.";
			}
		}
		
		$filenameSampleSolution = $this->getImageFilenameBestsolution();
		
		if (!empty($filenameSampleSolution)) {
		    if (!copy($imagepath_original . $filenameSampleSolution, $imagepath . $filenameSampleSolution))
		    {
		        print "Image could not be copied.";
		    }
		}
	}
	
	/**
	 * Copies a question
	 * This is used when a question is copied from a test to a question pool
	 *
	 * @access public
	 */
	public function createNewOriginalFromThisDuplicate($targetParentId, $targetQuestionTitle = "")
	{
		if ($this->id <= 0)
		{
			// The question has not been saved. It cannot be duplicated
			return;
		}
		
		include_once ("./Modules/TestQuestionPool/classes/class.assQuestion.php");
		
		$sourceQuestionId = $this->id;
		$sourceParentId = $this->getObjId();
		
		// duplicate the question in database
		$clone = $this;
		$clone->id = -1;
		
		$clone->setObjId($targetParentId);
		
		if ($targetQuestionTitle)
		{
			$clone->setTitle($targetQuestionTitle);
		}
		
		$clone->saveToDb();
		// copy question page content
		$clone->copyPageOfQuestion($sourceQuestionId);
		// copy XHTML media objects
		$clone->copyXHTMLMediaObjectsOfQuestion($sourceQuestionId);
		// duplicate the image
		$clone->copyImage($sourceQuestionId, $sourceParentId);
		
		$clone->onCopy($sourceParentId, $sourceQuestionId, $clone->getObjId(), $clone->getId());
		
		return $clone->id;
	}
	
	/**
	 * Get a submitted solution array from $_POST
	 *
	 * In general this may return any type that can be stored in a php session
	 * The return value is used by:
	 * 		savePreviewData()
	 * 		saveWorkingData()
	 * 		calculateReachedPointsForSolution()
	 *
	 * @return	array	('value1' => string, 'value2' => string, 'points' => float)
	 */
	protected function getSolutionSubmit()
	{
		return array(
				'value1' => ilUtil::stripSlashes($_POST['answerJSON'."_qst_" . $this->getId()], false),				
				'value2' => ilUtil::stripSlashes($_POST['answerImage'."_qst_" . $this->getId()])
		);
	}

	/**
	 * Returns the points, a learner has reached answering the question
	 * The points are calculated from the given answers including checks
	 * for all special scoring options in the test container.
	 *
	 * @param integer $user_id The database ID of the learner
	 * @param integer $test_id The database Id of the test containing the question
	 * @param boolean $returndetails (deprecated !!)
	 * @access public
	 */
	function calculateReachedPoints($active_id, $pass = NULL, $authorizedSolution = true, $returndetails = false)
	{
		global $ilDB;
		
		if (is_null($pass))
		{
			$pass = $this->getSolutionMaxPass($active_id);
		}
		/*
		$result = $ilDB->queryF("SELECT * FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
			array(
				"integer", 
				"integer",
				"integer"
			),
			array(
				$active_id,
				$this->getId(),
				$pass
			)
		);
		*/
		$points = 0; // manuelle korrektur notwendig		
		/*
		$data = $ilDB->fetchAssoc($result);
		$value1 = $data['value1'];
		$value2 = $data['value2'];		
		*/
		return $points;
	}

	/**
	 * Calculate the reached points for a submitted user input
	 *
	 * @param mixed user input (scalar, object or array)
	 */
	public function calculateReachedPointsforSolution($solution)
	{
		return 0;
	}
	
    /**
	* Returns the filesystem path for file uploads
	*/
	protected function getFileUploadPath($test_id, $active_id)
	{
		$question_id = $this->getId();
		return CLIENT_WEB_DIR . "/assessment/tst_$test_id/$active_id/$question_id/files/";
	}
	
	/**
	 * Saves the learners input of the question to the database
	 *
	 * @param integer $active_id 	Active id of the user
	 * @param integer $pass 		Test pass
	 * @param boolean $authorized	The solution is authorized
	 *
	 * @return boolean $status
	 */
	function saveWorkingData($active_id, $pass = NULL, $authorized = true)
	{
		global $ilDB;
		global $ilUser;
		if (is_null($pass))
		{
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			$pass = ilObjTest::_getPass($active_id);
		}
		
		$entered_values = false;
		$solution = $this->getSolutionSubmit();

		// Get part of the path the image will be saved at
		$result = $ilDB->queryF("SELECT test_fi FROM tst_active WHERE active_id = %s",
			array('integer'),
			array($active_id)
		);
		$test_id = 0;
		if ($result->numRows() == 1)
		{
			$row = $ilDB->fetchAssoc($result);
			$test_id = $row["test_fi"];
		}

		$this->getProcessLocker()->executeUserSolutionUpdateLockOperation(function () use (&$entered_values, $active_id, $pass, $authorized, $solution, $test_id) {

			$this->removeCurrentSolution($active_id, $pass, $authorized);
			
			if (strlen($solution["value2"]) > 0) {
				$microtime = round(microtime(true) * 1000);
				$filename = $this->getFileUploadPath($test_id, $active_id).$microtime."_PaintTask_" . $pass . ".png";
				
				if (!@file_exists($this->getFileUploadPath($test_id, $active_id)))
					ilUtil::makeDirParents($this->getFileUploadPath($test_id, $active_id));
					
					// Dont't delete old solutions as long as the test or the specific test pass exists: comment unlink
					// Grab all files from the desired folder
					$files_draw_layer = glob( $this->getFileUploadPath($test_id, $active_id).'*PaintTask_' . $pass . '.png' );
					$files_full_backup = glob( $this->getFileUploadPath($test_id, $active_id).'*full_backup_' . $pass . '.png' );
					
					$counter =  $this->getEnableForUsersConf() ? $this->getLogCount() : $this->getLogCountConf();
					
					if (count($files_draw_layer) >= $counter)
					{
						usort($files_draw_layer, function($a, $b) {
							return intval(explode('_', $a)[0]) < intval(explode('_', $b)[0]);
						});
							unlink($files_draw_layer[0]); // delete oldest file
					}
					
					if (count($files_full_backup) >= $counter)
					{
						usort($files_full_backup, function($a, $b) {
							return intval(explode('_', $a)[0]) < intval(explode('_', $b)[0]);
						});
							unlink($files_full_backup[0]); // delete oldest file
					}
					
					$matches = array();
					if(preg_match('/^data:image\/png;base64,(?<base64>.+)$/', $solution["value2"], $matches) === 1) {
						file_put_contents($filename, base64_decode($matches['base64']));
						// Option to save the complete presentation into the log instead the plain participants drawing
						
						$backgroundLog =  $this->getEnableForUsersConf() ? $this->getLogBkgr() : $this->getLogBkgrConf();
						
						if ($backgroundLog && $this->getImageFilename()) {
							
							//get background and save in var
							if ($this->getImageFilename())
							{
								$pathToImage = $this->getImagePath() . $this->getImageFilename();
								
								list ( $width, $height, $type ) = getimagesize ( $pathToImage );
								
								switch ( $type )
								{
									case 1:
										$background = imagecreatefromgif ($pathToImage);
										break;
									case 2:
										$background = imagecreatefromjpeg ($pathToImage);
										break;
									case 3:
										$backgroundInput = imagecreatefrompng ($pathToImage);
										// Steps to convert transparency to white (instead of default black)
										$backgroundWidth = imagesx($backgroundInput);
										$backgroundHeight = imagesy($backgroundInput);
										$background = imagecreatetruecolor($backgroundWidth, $backgroundHeight);
										$white = imagecolorallocate($background,  255, 255, 255);
										imagefilledrectangle($background, 0, 0, $backgroundWidth, $backgroundHeight, $white);
										imagecopy($background, $backgroundInput, 0, 0, 0, 0, $backgroundWidth, $backgroundHeight);
								}
								//predefine picture in case no drawing exists -> show only background image
								ob_start();
								imagepng($background);
								$image = ob_get_clean();
								$base64 = base64_encode( $image );
							} else
							{
								//transparent pixel, no background
								//will be overwritten if drawing exists
								$base64 = "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVQYV2NgYAAAAAMAAWgmWQ0AAAAASUVORK5CYII=";
							}
							if ($this->getRadioOption() == "radioOwnSize")
							{
								
							} else // radioImageSize
							{
								if( $this->getImageFilename() )
								{
									$image = $this->getImagePath() . $this->getImageFilename();
									$size = getimagesize($image);
								}
							}
							
							$content = file_get_contents ( $filename);
							
							//merge background and drawing if backgroundimage available
							if( $this->getImageFilename() )
							{
								$drawing = imagecreatefromstring($content);
								
								$x1 = imagesx($background);
								$y1 = imagesy($background);
								$x2 = imagesx($drawing);
								$y2 = imagesy($drawing);
								
								imagecopyresampled(
										$background, $drawing,
										0, 0, 0, 0,
										$x1, $y1,
										$x2, $y2);
								
								ob_start();
								//resizing the picture to custom values
								if ($this->getRadioOption() == "radioOwnSize")
								{
									$resized=imagecreatetruecolor($this->getCanvasWidth(),$this->getCanvasHeight());
									imagecopyresampled($resized,$background,0,0,0,0,$this->getCanvasWidth(),$this->getCanvasHeight(),$x1,$y1);
									imagepng($resized);
								} else //use original background
								{
									imagepng($background);
								}
								$image = ob_get_clean();
								$base64 = base64_encode( $image );
								imagedestroy($background);
								imagedestroy($drawing);
							} else //only use the drawing
							{
								$base64 = base64_encode( $content );
							}
							file_put_contents($this->getFileUploadPath($test_id, $active_id).$microtime.'_PaintTask_full_backup_' . $pass . '.png' , base64_decode($base64));
						}
					} else {
						throw new InvalidArgumentException("failed to decode and save image.");
					}

				$this->saveCurrentSolution($active_id, $pass, $solution["value1"], $filename, $authorized);
				$entered_values = true;
			}
		});
			
		// Log whether the user entered values
		if (ilObjAssessmentFolder::_enabledAssessmentLogging())
		{
			assQuestion::logAction($this->lng->txtlng(
					'assessment',
					$entered_values ? 'log_user_entered_values' : 'log_user_not_entered_values',
					ilObjAssessmentFolder::_getLogLanguage()
					),
					$active_id,
					$this->getId()
					);
		}
		
		// submitted solution is valid
		return true;
		
		
	}

	/**
	 * Reworks the allready saved working data if neccessary
	 *
	 * @abstract
	 * @access protected
	 * @param integer $active_id
	 * @param integer $pass
	 * @param boolean $obligationsAnswered
	 */
	protected function reworkWorkingData($active_id, $pass, $obligationsAnswered, $authorized)
	{
		// nothing to rework!		
	}

	/**
	* Returns the question type of the question
	*
	* @return integer The question type of the question
	* @access public
	*/
	function getQuestionType()
	{
		return "assPaintQuestion";
	}
	
	/**
	* Returns the name of the additional question data table in the database
	*
	* @return string The additional table name
	* @access public
	*/
	function getAdditionalTableName()
	{
		return array('il_qpl_qst_paint_check',
				'il_qpl_qst_paint_image');
	}
	
	/**
	* Returns the name of the answer table in the database
	*
	* @return string The answer table name
	* @access public
	*/
	function getAnswerTableName()
	{
		return "";
	}

	/**
	* Collects all text in the question which could contain media objects
	* which were created with the Rich Text Editor
	*/
	function getRTETextWithMediaObjects()
	{
		$text = parent::getRTETextWithMediaObjects();
		return $text;
	}
	
	/**
	* Creates an Excel worksheet for the detailed cumulated results of this question
	*
	* @param object $worksheet Reference to the parent excel worksheet
	* @param object $startrow Startrow of the output in the excel worksheet
	* @param object $active_id Active id of the participant
	* @param object $pass Test pass
	* @param object $format_title Excel title format
	* @param object $format_bold Excel bold format
	* @param array $eval_data Cumulated evaluation data
	* @access public
	*/
	public function setExportDetailsXLS($worksheet, $startrow, $active_id, $pass)
	{	
		
		parent::setExportDetailsXLS($worksheet, $startrow, $active_id, $pass);
		
		//BASE64-String in Excel won't make much sense, so leave the qst
		/*
		include_once ("./Services/Excel/classes/class.ilExcelUtils.php");
		$solution = $this->getSolutionValues($active_id, $pass);
		$worksheet->writeString($startrow, 0, ilExcelUtils::_convert_text($this->lng->txt($this->getQuestionType())), $format_title);
		$worksheet->writeString($startrow, 1, ilExcelUtils::_convert_text($this->getTitle()), $format_title);
		$i = 1;
		
		//$worksheet->writeString($startrow + $i, 0, $solutionvalue["value1"], $format_bold);
		//$worksheet->writeString($startrow + $i, 1, $solutionvalue["value2"]);		
		//$i++;					
		*/
		
		return $startrow + 1;
	}	
		
	/**
	* Creates a question from a QTI file
	*
	* Receives parameters from a QTI parser and creates a valid ILIAS question object
	*
	* @param object $item The QTI item object
	* @param integer $questionpool_id The id of the parent questionpool
	* @param integer $tst_id The id of the parent test if the question is part of a test
	* @param object $tst_object A reference to the parent test object
	* @param integer $question_counter A reference to a question counter to count the questions of an imported question pool
	* @param array $import_mapping An array containing references to included ILIAS objects
	* @access public
	*/
	function fromXML(&$item, &$questionpool_id, &$tst_id, &$tst_object, &$question_counter, &$import_mapping)
	{
		$this->getPlugin()->includeClass("import/qti12/class.assPaintQuestionImport.php");
		$import = new assPaintQuestionImport($this);
		$import->fromXML($item, $questionpool_id, $tst_id, $tst_object, $question_counter, $import_mapping);
	}
	
	/**
	* Returns a QTI xml representation of the question and sets the internal
	* domxml variable with the DOM XML representation of the QTI xml representation
	*
	* @return string The QTI xml representation of the question
	* @access public
	*/
	function toXML($a_include_header = true, $a_include_binary = true, $a_shuffle = false, $test_output = false, $force_image_references = false)
	{
		$this->getPlugin()->includeClass("export/qti12/class.assPaintQuestionExport.php");
		$export = new assPaintQuestionExport($this);
		return $export->toXML($a_include_header, $a_include_binary, $a_shuffle, $test_output, $force_image_references);
	}
}
?>
