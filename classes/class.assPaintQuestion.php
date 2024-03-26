<?php

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
    protected $plugin = null;	
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
	 * Constructor
	 *
	 * The constructor takes possible arguments and creates an instance of the question object.
	 *
	 * @param string $title A title string to describe the question
	 * @param string $comment A comment string to describe the question
	 * @param string $author A string containing the name of the questions author
	 * @param integer $owner A numerical ID to identify the owner/creator
	 * @param string $question Question text
	 * @access public
	 *
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
	 * Returns the question type of the question
	 *
	 * @return string The question type of the question
	 */
	function getQuestionType() : string
	{
	    return "assPaintQuestion";
	}
	
	/**
	 * Returns the names of the additional question data tables
	 *
	 * All tables must have a 'question_fi' column.
	 * Data from these tables will be deleted if a question is deleted
	 *
	 * @return mixed 	the name(s) of the additional tables (array or string)
	 */
	function getAdditionalTableName()
	{
	    return array(  'il_qpl_qst_paint_check',
	                   'il_qpl_qst_paint_image');
	}

	/**
	 * Collects all texts in the question which could contain media objects
	 * which were created with the Rich Text Editor
	 */
	protected function getRTETextWithMediaObjects(): string
	{
	    $text = parent::getRTETextWithMediaObjects();
	    return $text;
	}
	
	/**
	 * Get the plugin object
	 *
	 * @return object The plugin object
	 */
	public function getPlugin() {
	    global $DIC;
	    
	    if ($this->plugin == null)
	    {
	        /** @var ilComponentFactory $component_factory */
	        $component_factory = $DIC["component.factory"];
	        $this->plugin = $component_factory->getPlugin('assPaintQuestion');
	    }
	    return $this->plugin;
	}
	
	/**
	 * Returns true, if the question is complete
	 *
	 * @return boolean True, if the question is complete for use, otherwise false
	 */
	public function isComplete(): bool
	{
		// Please add here your own check for question completeness
		// The parent function will always return false
	    if(!empty($this->title) && !empty($this->author) && !empty($this->question) && $this->getMaximumPoints() > 0)
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
	    global $DIC;
	    $ilDB = $DIC->database();
	    
		$file = $this->getImagePath() . $this->getImageFilename();
		$file_resized = $this->getImagePath() ."resized_".$this->getImageFilename();
		@unlink($file); // delete image from folder
		@unlink($file_resized);
		$this->image_filename = "";
		$ilDB->manipulate('update il_qpl_qst_paint_check set '.'resized = ' . 0 .' '.'WHERE question_fi = '.$this->getId());
	}

	function deleteImageBestsolution()
	{
	    global $DIC;
	    $ilDB = $DIC->database();
	    
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
		$this->resizedImageStatus = $value;
	}
	
	function getResizedImageStatus()
	{
		return $this->resizedImageStatus;
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
			    ilFileUtils::makeDirParents($imagepath);
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
	            ilFileUtils::makeDirParents($imagepath);
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
		
	    global $DIC;
	    $ilDB = $DIC->database();
	    
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
	 * Saves a question object to a database
	 *
	 * @param	string		$original_id
	 * @access 	public
	 * @see assQuestion::saveToDb()
	 */
	function saveToDb($original_id = ''): void
	{
	    global $DIC;
	    $ilDB = $DIC->database();
	    
	    // save the basic data (implemented in parent)
	    // a new question is created if the id is -1
	    // afterwards the new id is set
	    if ($original_id == '') {
	        $this->saveQuestionDataToDb();
	    } else {
	        $this->saveQuestionDataToDb($original_id);
	    }
	    
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
	 * Loads a question object from a database
	 * This has to be done here (assQuestion does not load the basic data)!
	 *
	 * @param integer $question_id A unique key which defines the question in the database
	 * @see assQuestion::loadFromDb()
	 */
	public function loadFromDb($question_id) : void
	{
	    global $DIC;
	    $ilDB = $DIC->database();
	    
	    // load the basic question data
	    $result = $ilDB->query("SELECT qpl_questions.* FROM qpl_questions WHERE question_id = "
	        . $ilDB->quote($question_id, 'integer'));
	    if ($result->numRows() > 0) {
	        $data = $ilDB->fetchAssoc($result);
	        $this->setId($question_id);
	        $this->setObjId($data['obj_fi']);
	        $this->setOriginalId($data['original_id']);
	        $this->setOwner($data['owner']);
	        $this->setTitle((string) $data['title']);
	        $this->setAuthor($data['author']);
	        $this->setPoints($data['points']);
	        $this->setComment((string) $data['description']);
	        //$this->setSuggestedSolution($data["solution_hint"]);
	        
	        try {
	            $this->setAdditionalContentEditingMode($data['add_cont_edit_mode']);
	        } catch (ilTestQuestionPoolException $e) {
	        }
	        
	        $this->setQuestion(ilRTE::_replaceMediaObjectImageSrc((string) $data['question_text'], 1));
	        
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
	    }

	    // loads additional stuff like suggested solutions
	    parent::loadFromDb($question_id);
	}

	/**
	 * Duplicates a question
	 * This is used for copying a question to a test
	 *
	 * @access public
	 */
	function duplicate($for_test = true, $title = "", $author = "", $owner = "", $testObjId = null) : int
	{
	    if ($this->getId() <= 0)
	    {
	        // The question has not been saved. It cannot be duplicated
	        return 0;
	    }
	    
	    // make a real clone to keep the object unchanged
	    $clone = clone $this;
	    
	    $original_id = assQuestion::_getOriginalId($this->getId());
	    $clone->setId(-1);
	    
	    if( (int) $testObjId > 0 )
	    {
	        $clone->setObjId($testObjId);
	    }
	    
	    if (!empty($title))
	    {
	        $clone->setTitle($title);
	    }
	    if (!empty($author))
	    {
	        $clone->setAuthor($author);
	    }
	    if (!empty($owner))
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
	    $clone->copyPageOfQuestion($this->getId());
	    // copy XHTML media objects
	    $clone->copyXHTMLMediaObjectsOfQuestion($this->getId());
	    
		// duplicate the image
	    $clone->duplicateImage($this->getId(), $this->getObjId());
		
		$clone->onDuplicate($this->getObjId(), $this->getId(), $clone->getObjId(), $clone->getId());
		
		return $clone->getId();
	}

	/**
	 * Copies a question
	 * This is used when a question is copied on a question pool
	 *
	 * @param integer	$target_questionpool_id
	 * @param string	$title
	 *
	 * @return void|integer Id of the clone or nothing.
	 */
	function copyObject($target_questionpool_id, $title = "")
	{
	    if ($this->getId() <= 0)
	    {
	        // The question has not been saved. It cannot be duplicated
	        return;
	    }
	    
	    // make a real clone to keep the object unchanged
	    $clone = clone $this;
	    
	    $original_id = assQuestion::_getOriginalId($this->getId());
	    $source_questionpool_id = $this->getObjId();
	    $clone->setId(-1);
	    $clone->setObjId($target_questionpool_id);
	    if (!empty($title))
	    {
	        $clone->setTitle($title);
	    }
	    
	    // save the clone data
	    $clone->saveToDb();

	    // copy question page content
	    $clone->copyPageOfQuestion($original_id);
	    // copy XHTML media objects
	    $clone->copyXHTMLMediaObjectsOfQuestion($original_id);
	    
		// duplicate the image
		$clone->copyImage($original_id, $source_questionpool_id);
		
		// call the event handler for copy
		$clone->onCopy($source_questionpool_id, $original_id, $clone->getObjId(), $clone->getId());
		
		return $clone->getId();
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
		    ilFileUtils::makeDirParents($imagepath);
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
		    ilFileUtils::makeDirParents($imagepath);
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
	 * Create a new original question in a question pool for a test question
	 * @param int $targetParentId			id of the target question pool
	 * @param string $targetQuestionTitle
	 * @return int|void
	 */
	public function createNewOriginalFromThisDuplicate($targetParentId, $targetQuestionTitle = "")
	{
		if ($this->id <= 0)
		{
			// The question has not been saved. It cannot be duplicated
			return;
		}
				
		$sourceQuestionId = $this->id;
		$sourceParentId = $this->getObjId();
		
		// make a real clone to keep the object unchanged
		$clone = clone $this;
		$clone->setId(-1);
		
		$clone->setObjId($targetParentId);
		
		if (!empty($targetQuestionTitle))
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
		
		return $clone->getId();
	}
	
	/**
	 * Get the submitted user input as a serializable value
	 *
	 * @return mixed user input (scalar, object or array)
	 */
	protected function getSolutionSubmit()
	{
	    $value1 = isset($_POST['answerJSON'."_qst_" . $this->getId()]) ? trim(ilUtil::stripSlashes($_POST['answerJSON'."_qst_" . $this->getId()], false)) : null;
	    $value2 = isset($_POST['answerImage'."_qst_" . $this->getId()]) ? trim(ilUtil::stripSlashes($_POST['answerImage'."_qst_" . $this->getId()])) : null;
	    
	    return array(
	        'value1' => empty($value1)? null : (string) $value1,
	        'value2' => empty($value2)? null : (string) $value2
	    );
	}
	
	/**
	 * Get a stored solution for a user and test pass
	 * This is a wrapper to provide the same structure as getSolutionSubmit()
	 *
	 * @param int 	$active_id		active_id of hte user
	 * @param int	$pass			number of the test pass
	 * @param bool	$authorized		get the authorized solution
	 *
	 * @return	array	('value1' => string|null, 'value2' => string|null, 'value3' => string|null, 'value4' => string|null)
	 */
	public function getSolutionStored($active_id, $pass, $authorized = null)
	{
	    // This provides an array with records from tst_solution
	    // The example question should only store one record per answer
	    // Other question types may use multiple records with value1/value2 in a key/value style
	    if (isset($authorized))
	    {
	        // this provides either the authorized or intermediate solution
	        $solutions = $this->getSolutionValues($active_id, $pass, $authorized);
	    }
	    else
	    {
	        // this provides the solution preferring the intermediate
	        // or the solution from the previous pass
	        $solutions = $this->getTestOutputSolutions($active_id, $pass);
	    }
	    
	    
	    if (empty($solutions))
	    {
	        // no solution stored yet
	        $value1 = null;
	        $value2 = null;
	    }
	    else
	    {
	        // If the process locker isn't activated in the Test and Assessment administration
	        // then we may have multiple records due to race conditions
	        // In this case the last saved record wins
	        $solutions = end($solutions);
	        
	        $value1 = $solutions['value1'];
	        $value2 = $solutions['value2'];
	        
	    }
	    
	    return array(
	        'value1' => empty($value1)? null : (string) $value1,
	        'value2' => empty($value2)? null : (string) $value2
	    );
	}
	
	/**
	 * Calculate the reached points for a submitted user input
	 *
	 * @return  float	reached points
	 */
	public function calculateReachedPointsforSolution($solution)
	{
	    // paint must be graded manually
	    return 0;
	}
	
	/**
	 * Returns the points, a learner has reached answering the question
	 * The points are calculated from the given answers.
	 *
	 * @param integer $active 	The Id of the active learner
	 * @param integer $pass 	The Id of the test pass
	 * @param boolean $returndetails (deprecated !!)
	 * @return integer/array $points/$details (array $details is deprecated !!)
	 * @access public
	 * @see  assQuestion::calculateReachedPoints()
	 */
	function calculateReachedPoints($active_id, $pass = NULL, $authorizedSolution = true, $returndetails = false)
	{
	    if( $returndetails )
	    {
	        throw new ilTestException('return details not implemented for '.__METHOD__);
	    }
	    
		global $ilDB;
		
		if (is_null($pass))
		{
			$pass = $this->getSolutionMaxPass($active_id);
		}

		$solution = $this->getSolutionStored($active_id, $pass, $authorizedSolution);
		return $this->calculateReachedPointsForSolution($solution);
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
	 * @param 	integer $test_id The database id of the test containing this question
	 * @return 	boolean Indicates the save status (true if saved successful, false otherwise)
	 * @access 	public
	 * @see 	assQuestion::saveWorkingData()
	 */
	function saveWorkingData($active_id, $pass = NULL, $authorized = true) : bool
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
				    ilFileUtils::makeDirParents($this->getFileUploadPath($test_id, $active_id));
					
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
	 * @access protected
	 * @param integer $active_id
	 * @param integer $pass
	 * @param boolean $obligationsAnswered
	 */
	protected function reworkWorkingData($active_id, $pass, $obligationsAnswered, $authorized)
	{
	    // normally nothing needs to be reworked
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
	 * Creates an Excel worksheet for the detailed cumulated results of this question
	 *
	 * @access public
	 * @see assQuestion::setExportDetailsXLS()
	 */
	public function setExportDetailsXLS(ilAssExcelFormatHelper $worksheet, int $startrow, int $active_id, int $pass): int
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
}
?>
