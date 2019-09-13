<?php

include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
 * Class for TemplateQuestion Question
 *
 * @author Yves Annanias <yves.annanias@llz.uni-halle.de>
 * @version	$Id:  $
 * @ingroup ModulesTestQuestionPool
 */
class assPaintQuestion extends assQuestion
{
	private $plugin = null;	
	// backgroundimage	
	var $image_filename = "";
	// brushsize choosable? false - 0, true - 1
	var $lineValue = 0;
	// colourselection enabled? false - 0, true - 1
	var $colorValue = 0;	
	// canvas size for backgroundimage or individual
	var $radioOption = 'radioImageSize';
	// canvas width
	var $canvasWidth = 100;
	// canvas height
	var $canvasHeight = 100;
	
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
	
	function deleteImage()
	{
		$file = $this->getImagePath() . $this->getImageFilename();
		@unlink($file); // delete image from folder
		$this->image_filename = "";
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
		if ($value == 1)
			$this->lineValue = 1;
		else
			$this->lineValue = 0;			
	}
	
	function setColorValue($value)
	{
		if ($value == 1)
			$this->colorValue = 1;
		else
			$this->colorValue = 0;
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

		// load backgroundImage
		$resultImage= $ilDB->queryF("SELECT image_file FROM il_qpl_qst_paint_image WHERE question_fi = %s", array('integer'), array($question_id));
		if($ilDB->numRows($resultImage) == 1)
		{
			$data = $ilDB->fetchAssoc($resultImage);
			$this->image_filename = $data["image_file"];
		}		
		
		$resultCheck= $ilDB->queryF("SELECT line, color, radio_option, width, height FROM il_qpl_qst_paint_check WHERE question_fi = %s", array('integer'), array($question_id));
		if($ilDB->numRows($resultCheck) == 1)
		{
			$data = $ilDB->fetchAssoc($resultCheck);
			if ($data["line"]==1)
				$this->lineValue = 1;
			else $this->lineValue = 0;
			$this->colorValue = $data["color"];
			$this->setRadioOption($data["radio_option"]);
			$this->setCanvasWidth($data["width"]);
			$this->setCanvasHeight($data["height"]);
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
		if (!empty($this->image_filename))
		{
			$affectedRows = $ilDB->manipulateF("INSERT INTO il_qpl_qst_paint_image (question_fi, image_file) VALUES (%s, %s)", 
				array("integer", "text"),
				array(
					$this->getId(),
					$this->image_filename
				)
			);
		}
		// save line and color option
		$affectedRows = $ilDB->manipulateF("DELETE FROM il_qpl_qst_paint_check WHERE question_fi = %s", 
			array("integer"),
			array($this->getId())
		);
		$affectedRows = $ilDB->manipulateF("INSERT INTO il_qpl_qst_paint_check (question_fi, line, color, radio_option, width, height) VALUES (%s, %s, %s, %s, %s, %s)", 
				array("integer", "integer", "integer", "text", "integer", "integer"),
				array(
					$this->getId(),
					$this->lineValue,
					$this->colorValue,
					$this->radioOption,
					$this->canvasWidth,
					$this->canvasHeight
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
		
		if (!copy($imagepath_original . $filename, $imagepath . $filename)) 
		{
			print "Image could not be copied.";
		}
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
	* @param integer $test_id The database id of the test containing this question
    * @return boolean Indicates the save status (true if saved successful, false otherwise)
	* @access public
	* @see $answers
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

		$affectedRows = $ilDB->manipulateF("DELETE FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
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

		$entered_values = false;		
		$value = $_POST['answerImage'];		
		
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
		
		if (strlen($value) > 0)
		{
			$microtime = round(microtime(true) * 1000);
			$filename = $this->getFileUploadPath($test_id, $active_id).$microtime."_PaintTask.png";
			$entered_values = true;
			$next_id = $ilDB->nextId("tst_solutions");
			$affectedRows = $ilDB->insert("tst_solutions", array(
				"solution_id" => array("integer", $next_id),
				"active_fi" => array("integer", $active_id),
				"question_fi" => array("integer", $this->getId()),
				"value1" => array("clob", 'path'),
				"value2" => array("clob", $filename),
				"pass" => array("integer", $pass),
				"tstamp" => array("integer", time())
			));

			if (!@file_exists($this->getFileUploadPath($test_id, $active_id)))
				ilUtil::makeDirParents($this->getFileUploadPath($test_id, $active_id));

			// Grab all files from the desired folder
			$files = glob( $this->getFileUploadPath($test_id, $active_id).'*.png' );
			if (count($files) >= 3)
			{
				usort($files, function($a, $b) {
					return intval(explode('_', $a)[0]) < intval(explode('_', $b)[0]);
				});
				unlink($files[0]); // delete oldest file
			}

			$matches = array();
			if(preg_match('/^data:image\/png;base64,(?<base64>.+)$/', $value, $matches) === 1) {
				file_put_contents($filename, base64_decode($matches['base64']));
			} else {
				throw new InvalidArgumentException("failed to decode and save image.");
			}
		}
		
		if ($entered_values)
		{
			include_once ("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$this->logAction($this->lng->txtlng("assessment", "log_user_entered_values", ilObjAssessmentFolder::_getLogLanguage()), $active_id, $this->getId());				
			}
		}
		else
		{
			include_once ("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$this->logAction($this->lng->txtlng("assessment", "log_user_not_entered_values", ilObjAssessmentFolder::_getLogLanguage()), $active_id, $this->getId());
			}
		}		
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
		return "";
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
