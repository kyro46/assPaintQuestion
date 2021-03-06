<?php

include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

 /**
 * The assPaintQuestionGUI class encapsulates the GUI representation
 * for Question-Type-Plugin.
 *
 * @author Yves Annanias <yves.annanias@llz.uni-halle.de>
 * @author Christoph Jobst <cjobst@wifa.uni-leipzig.de>
 * @ingroup ModulesTestQuestionPool
 *
 * @ilctrl_iscalledby assPaintQuestionGUI: ilObjQuestionPoolGUI, ilObjTestGUI, ilQuestionEditGUI, ilTestExpressPageObjectGUI
 * @ilCtrl_Calls assPaintQuestionGUI: ilFormPropertyDispatchGUI
 */
class assPaintQuestionGUI extends assQuestionGUI
{		
	var $plugin = null;

	/**
	 * Constructor
	 *
	 * @param integer $id The database id of a question object
	 * @access public
	 */
	public function __construct($id = -1)
	{
		parent::__construct();
		include_once "./Services/Component/classes/class.ilPlugin.php";
		$this->plugin = ilPlugin::getPluginObject(IL_COMP_MODULE, "TestQuestionPool", "qst", "assPaintQuestion");
		$this->plugin->includeClass("class.assPaintQuestion.php");
		$this->object = new assPaintQuestion();
		if ($id >= 0)
		{
			$this->object->loadFromDb($id);
		}
	}	
	
	/**
	 * Creates an output of the edit form for the question
	 *
	 * @param bool $checkonly
	 * @return bool
	 */
	public function editQuestion($checkonly = FALSE)
	{
		global $ilDB;					

		$save = $this->isSaveCommand();
		$plugin = $this->object->getPlugin();		
		
		$this->getQuestionTemplate();
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->outQuestionType());
		$form->setMultipart(FALSE);
		$form->setTableWidth("100%");
		$form->setId("assPaintQuestion");
		// Baseinput: title, author, description, question, working time (assessment mode)		
		$this->addBasicQuestionFormProperties($form);
		
		//Start Question specific
		// points
		$points = new ilNumberInputGUI($plugin->txt("points"), "points");
		$points->setSize(3);
		$points->setMinValue(1);
		$points->allowDecimals(1);
		$points->setRequired(true);
		$points->setValue($this->object->getPoints());
		$form->addItem($points);
		
		// background-image		
		$image = new ilImageFileInputGUI($plugin->txt("image"), 'imagefile');
		$image->setSuffixes(array("jpg", "jpeg", "png"));
		
		if ($this->object->getImageFilename() != "")
		{
			$image->setImage($this->object->getImagePathWeb().$this->object->getImageFilename());
		}
		$form->addItem($image);
		
		//cancassize
		$canvasArea = new ilRadioGroupInputGUI($plugin->txt("canvasArea"), "canvasArea");
		$canvasArea->addOption(new ilRadioOption($plugin->txt("useImageSize"), 'radioImageSize', ''));
		$canvasArea->setInfo($plugin->txt("canvas_size_hint"));
		$ownSize = new ilRadioOption($plugin->txt("useOwnSize"), 'radioOwnSize', '');
		$canvasArea->addOption($ownSize);
		$canvasArea->setValue($this->object->getRadioOption());
		
		$sizeWidth = new ilNumberInputGUI($plugin->txt("width"),"sizeWidth");
		$sizeWidth->setValue($this->object->getCanvasWidth());		
		$sizeWidth->setSize(6);
		$sizeWidth->setMinValue(450);
		
		$sizeHeight = new ilNumberInputGUI($plugin->txt("height"),"sizeHeight");
		$sizeHeight->setValue($this->object->getCanvasHeight());
		$sizeHeight->setSize(6);
		$sizeHeight->setMinValue(400);
		
		$ownSize->addSubItem($sizeWidth);
		$ownSize->addSubItem($sizeHeight);
		$form->addItem($canvasArea);
		
		// brushsize
		$line = new ilCheckboxInputGUI($plugin->txt("line"), 'lineValue');
		if ($this->object->getLineValue())
			$line->setChecked(true);
		$form->addItem($line);
		
		// colourselection
		/*Remove this option with version 1.1.10
		$color = new ilCheckboxInputGUI($plugin->txt("color"), 'colorValue');
		if ($this->object->getColorValue())
			$color->setChecked(true);
		$form->addItem($color);	
		*/
		
		// sample solution
		$imageBestsolution = new ilImageFileInputGUI($plugin->txt("image_bestsolution"), 'imagefile_bestsolution');
		$imageBestsolution->setSuffixes(array("jpg", "jpeg", "png"));

		if ($this->object->getImageFilenameBestsolution() != "")
		{
		    $imageBestsolution->setImage($this->object->getImagePathWeb().$this->object->getImageFilenameBestsolution());
		}
		$form->addItem($imageBestsolution);
		
		if ($this->object->getEnableForUsersConf()) {
			//LogCount
			$logCountOption = new ilSelectInputGUI($plugin->txt("logCountOption"),"logCountValue");
			$logCountOption->setInfo($plugin->txt("logCountOption_hint"));
			$logCountOption->setOptions (Array ( "1" => $plugin->txt("logCountOption_off"), "3" => "3", "10" => "10", "50" => "50", "100" => "100"));
			$logCountOption->setValue($this->object->getLogCount());
			$form->addItem($logCountOption);
	
			//LogBkgr
			$logBkgrOption = new ilCheckboxInputGUI($plugin->txt("logBkgrOption"), 'logBkgrValue');
			$logBkgrOption->setInfo($plugin->txt("logBkgrOption_hint"));
			if ($this->object->getLogBkgr())
				$logBkgrOption->setChecked(true);
			$form->addItem($logBkgrOption);
		}
		
		$this->tpl->setVariable("QUESTION_DATA", $form->getHTML());		
		//End Question specific
		
		$this->populateTaxonomyFormSection($form);
		$this->addQuestionFormCommandButtons($form);

		$errors = false;

		if ($save)
		{
			$form->setValuesByPost();
			$errors = !$form->checkInput();
			$form->setValuesByPost(); // again, because checkInput now performs the whole stripSlashes handling and we need this if we don't want to have duplication of backslashes
			if ($errors) $checkonly = false;
		}

		if (!$checkonly)
		{
			$this->tpl->setVariable("QUESTION_DATA", $form->getHTML());
		}
		return $errors;
	}

	/**
	 * Evaluates a posted edit form and writes the form data in the question object
	 *
	 * @param bool $always
	 * @return integer A positive value, if one of the required fields wasn't set, else 0
	 */
	public function writePostData($always = false)
	{
	    $hasErrors = (!$always) ? $this->editQuestion(true) : false;
	    if (!$hasErrors)
	    {
	        $this->writeQuestionGenericPostData();
	        $this->object->setPoints( str_replace( ",", ".", $_POST["points"] ));
	        
	        //Background
	        if ($_POST['imagefile_delete'])
	        {
	            $this->object->deleteImage();
	        } else
	        {
	            if (strlen($_FILES['imagefile']['tmp_name']))
	            {
	                $this->object->deleteImage(); //Something (probably new) was uploaded - delete the old image
	                $this->object->setImageFilename($_FILES['imagefile']['name'], $_FILES['imagefile']['tmp_name']);
	            }
	        }
	        $this->object->setRadioOption($_POST["canvasArea"]);
	        $this->object->setCanvasWidth($_POST["sizeWidth"]);
	        $this->object->setCanvasHeight($_POST["sizeHeight"]);
	        $this->object->setLineValue($_POST['lineValue']);
	        $this->object->setColorValue($_POST['colorValue']);
	        
	        //Sample solution
	        if ($_POST['imagefile_bestsolution_delete'])
	        {
	            $this->object->deleteImageBestsolution();
	        } else
	        {
	            if (strlen($_FILES['imagefile_bestsolution']['tmp_name']))
	            {
	                $this->object->deleteImageBestsolution(); //Something (probably new) was uploaded - delete the old image
	                $this->object->setImageFilenameBestsolution($_FILES['imagefile_bestsolution']['name'], $_FILES['imagefile_bestsolution']['tmp_name']);
	            }
	        }
	        
	        if ($this->object->getEnableForUsersConf()) {
	           $this->object->setLogCount($_POST['logCountValue']);
	           $this->object->setLogBkgr($_POST['logBkgrValue']);
	        }

	        //Compute resized picture as early as possible
	        if ($this->object->getImageFilename() && $this->object->getRadioOption() == "radioOwnSize") {
	            $this->object->resizeImage( $this->object->getCanvasWidth(),$this->object->getCanvasHeight());
	        }
	        $this->saveTaxonomyAssignments();
	        return 0;
	    }
	    return 1;
	}

	/**
	* check input fields
	*/
	function checkInput()
	{		
		if ((!$_POST["title"]) or (!$_POST["author"]) or (!$_POST["question"]) or (strlen($_POST["points"]) == 0) or ($_POST["points"] < 0) )
		{			
			return FALSE;
		}	
		return TRUE;
	}
	
	/**
	 * Get the output for question preview
	 * (called from ilObjQuestionPoolGUI)
	 * 
	 * @param boolean	show only the question instead of embedding page (true/false)
	 */
	function getPreview($show_question_only = false, $showInlineFeedback = false)
	{		
		global $tpl;			
		$plugin       = $this->object->getPlugin();		
		$template     = $plugin->getTemplate("output_dev.html");						
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($this->object->getQuestion(), TRUE));
		if (!$this->object->getLineValue()) {
			$template->setVariable("DISPLAY_LINE", "8");
		} else {
			$template->setVariable("DISPLAY_LINE", "1, 5, 10, 20, 30");
		}

		$template->setVariable("PAINT_ID", "qst_" . $this->object->getId());
		
		if ($this->object->getImageFilename() && $this->object->getRadioOption() != "radioOwnSize") {
			$template->setVariable("BACKGROUND", $this->object->getImagePathWeb().$this->object->getImageFilename());
		}
		
		if ($this->object->getImageFilename() && $this->object->getRadioOption() == "radioOwnSize") {
			//TODO workaround this someday.
			//For now needed for old or imported questions. 
			if ($this->object->getResizedImageStatus() == 0){
				$this->object->resizeImage( $this->object->getCanvasWidth(),$this->object->getCanvasHeight());
				
			}
			$template->setVariable("BACKGROUND", $this->object->getImagePathWeb()."resized_".$this->object->getImageFilename());
		}


		if ($this->object->getRadioOption() == "radioOwnSize")
		{
			$template->setVariable("WIDTH", $this->object->getCanvasWidth() + 61);
			$template->setVariable("HEIGHT", $this->object->getCanvasHeight() + 31);
			$template->setVariable("HEIGHT_DIV", $this->object->getCanvasHeight() + 31);

		} else // use Image Size or default of 800x700
		{
			if( $this->object->getImageFilename() )
			{
				$image = $this->object->getImagePath().$this->object->getImageFilename();
				$size = getimagesize($image);
				$template->setVariable("WIDTH", $size[0] + 61);
				$template->setVariable("HEIGHT", $size[1] + 31);
				
				$height = $size[1] + 31;
				if ($height < 400) {
					$template->setVariable("HEIGHT_DIV", 400);
				} else {
					$template->setVariable("HEIGHT_DIV", $height);
				}
			} else
			{
				$template->setVariable("WIDTH", 861);
				$template->setVariable("HEIGHT", 731);
			}
		}
		
		$tpl->addCss("./Customizing/global/plugins/Modules/TestQuestionPool/Questions/assPaintQuestion/templates/_assets/literallycanvas.css");
		$tpl->addJavaScript($plugin->getDirectory().'/templates/_js_libs/react-0.14.3.js');
		$tpl->addJavaScript($plugin->getDirectory().'/templates/_js_libs/literallycanvas.js');		
		
		$template->setVariable("RESUME", "");
		
		$questionoutput = $template->get();
		if(!$show_question_only)
		{
			// get page object output
			$questionoutput = $this->getILIASPage($questionoutput);
		}
		
		return $questionoutput;
	}

	/**
	 * Get the HTML output of the question for a test
	 * 
	 * @param integer $active_id			The active user id
	 * @param integer $pass					The test pass
	 * @param boolean $is_postponed			Question is postponed
	 * @param boolean $use_post_solutions	Use post solutions
	 * @param boolean $show_feedback		Show a feedback
	 * @return string
	 */	
	function getTestOutput($active_id, $pass = NULL, $is_postponed = FALSE, $use_post_solutions = FALSE, $show_feedback = FALSE)
	{
		global $tpl;
		// get the solution of the user for the active pass or from the last pass if allowed
		$user_solution = array();
		if ($active_id)
		{
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			if (!ilObjTest::_getUsePreviousAnswers($active_id, true))
			{
				if (is_null($pass)) $pass = ilObjTest::_getPass($active_id);
			}
			$user_solution =& $this->object->getSolutionValues($active_id, $pass);
			if (!is_array($user_solution)) 
			{
				$user_solution = array();
			}
		}
		
		$plugin       = $this->object->getPlugin();		
		$template     = $plugin->getTemplate("output_dev.html");		
		$output 	  = $this->object->getQuestion();
		
		if (!$this->object->getLineValue()) {
			$template->setVariable("DISPLAY_LINE", "8");
		} else {
			$template->setVariable("DISPLAY_LINE", "1, 5, 10, 20, 30");
		}
		
		$template->setVariable("PAINT_ID", "qst_" . $this->object->getId());
		
		if ($this->object->getImageFilename() && $this->object->getRadioOption() != "radioOwnSize") {
			$template->setVariable("BACKGROUND", $this->object->getImagePathWeb().$this->object->getImageFilename());
		}
		
		if ($this->object->getImageFilename() && $this->object->getRadioOption() == "radioOwnSize") {
			//TODO workaround this someday.
			//For now needed for old or imported questions. 
			if ($this->object->getResizedImageStatus() == 0){
				$this->object->resizeImage( $this->object->getCanvasWidth(),$this->object->getCanvasHeight());
				
			}
			$template->setVariable("BACKGROUND", $this->object->getImagePathWeb()."resized_".$this->object->getImageFilename());
		}
		
				if ($this->object->getRadioOption() == "radioOwnSize")
				{
					$template->setVariable("WIDTH", $this->object->getCanvasWidth() + 61);
					$template->setVariable("HEIGHT", $this->object->getCanvasHeight() + 31);
					$template->setVariable("HEIGHT_DIV", $this->object->getCanvasHeight() + 31);
				} else // use Image Size or default of 800x700
				{
					if( $this->object->getImageFilename() )
					{
						$image = $this->object->getImagePath().$this->object->getImageFilename();
						$size = getimagesize($image);
						$template->setVariable("WIDTH", $size[0] + 61);
						$template->setVariable("HEIGHT", $size[1] + 31);
						
						$height = $size[1] + 31;
						if ($height < 400) {
							$template->setVariable("HEIGHT_DIV", 400);
						} else {
							$template->setVariable("HEIGHT_DIV", $height);
						}
					} else
					{
						$template->setVariable("WIDTH", 861);
						$template->setVariable("HEIGHT", 731);
					}
				}
		
		$tpl->addCss("./Customizing/global/plugins/Modules/TestQuestionPool/Questions/assPaintQuestion/templates/_assets/literallycanvas.css");
		$tpl->addJavaScript($plugin->getDirectory().'/templates/_js_libs/react-0.14.3.js');
		$tpl->addJavaScript($plugin->getDirectory().'/templates/_js_libs/literallycanvas.js');
		
		// letzte gespeicherte Eingabe anzeigen
		$base64 = "";
		if ($user_solution[0]["value2"])
		{
			// wenn eingabe vorhanden, dann bild von gegebener url als base64-string konvertieren
			$content = file_get_contents ( $user_solution[0]["value2"]);
			$base64 = 'data:image/png;base64,'.base64_encode( $content );
		}							
		
		if ($user_solution[0]["value2"] != 'path'){
			$template->setVariable("RESUMEJSON",preg_replace("{\\\}", "\\\\\\",$user_solution[0]["value1"]));
		}
		$template->setVariable("RESUME", ilUtil::prepareFormOutput($base64));	
		
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($output, TRUE));
		$questionoutput = $template->get();
		$pageoutput = $this->outQuestionPage("", $is_postponed, $active_id, $questionoutput);
		return $pageoutput;		
	}

	/**
	* Get the question solution output
	*
	* @param integer $active_id The active user id
	* @param integer $pass The test pass
	* @param boolean $graphicalOutput Show visual feedback for right/wrong answers
	* @param boolean $result_output Show the reached points for parts of the question
	* @param boolean $show_question_only Show the question without the ILIAS content around
	* @param boolean $show_feedback Show the question feedback
	* @param boolean $show_correct_solution Show the correct solution instead of the user solution
	* @param boolean $show_manual_scoring Show specific information for the manual scoring output
	* @return string The solution output of the question as HTML code
	*/
	function getSolutionOutput(
		$active_id,
		$pass = NULL,
		$graphicalOutput = FALSE,
		$result_output = FALSE,
		$show_question_only = TRUE,
		$show_feedback = FALSE,
		$show_correct_solution = FALSE,
		$show_manual_scoring = FALSE,
		$show_question_text = TRUE
	)
	{
		global $tpl;
		// get the solution of the user for the active pass or from the last pass if allowed
		$user_solution = array();
		if (($active_id > 0) && (!$show_correct_solution))
		{
			// get the solutions of a user
			$user_solution =& $this->object->getSolutionValues($active_id, $pass);
			if (!is_array($user_solution)) 
			{
				$user_solution = array();
			}
		} else {			
			$user_solution = array();
		}

		$plugin       = $this->object->getPlugin();		
		$template     = $plugin->getTemplate("solution.html");
		$output = $this->object->getQuestion();			
		
		if ($show_correct_solution)
		{	
		    if ($this->object->getImageFilenameBestsolution() != "") {
		      return "<img src='" . $this->object->getImagePathWeb() . $this->object->getImageFilenameBestsolution() . "'> ";
		    } else {
		        return $plugin->txt("not_set");
		    }

			//$template->setVariable("ID", $this->object->getId().'CORRECT_SOLUTION');	
			// TODO hier nur die Musterlösung anzeigen, da wir uns im test beim drücken von check befinden ;)
		}			
		else
			$template->setVariable("ID", $this->object->getId());		

		//get background and save in var
		if ($this->object->getImageFilename())
		{
			$pathToImage = $this->object->getImagePath().$this->object->getImageFilename();

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
					$background = imagecreatefrompng ($pathToImage);
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
		//preset formular
		$template->setVariable("SOLUTION", ilUtil::prepareFormOutput($base64));

		
		if ($this->object->getRadioOption() == "radioOwnSize")
		{
			$template->setVariable("WIDTH", $this->object->getCanvasWidth());
			$template->setVariable("HEIGHT", $this->object->getCanvasHeight());
		} else // radioImageSize
		{
			if( $this->object->getImageFilename() )
			{
				$image = $this->object->getImagePath().$this->object->getImageFilename();
				$size = getimagesize($image);
				$template->setVariable("WIDTH", $size[0]);
				$template->setVariable("HEIGHT", $size[1]);
			} else
			{
				$template->setVariable("WIDTH", 800);
				$template->setVariable("HEIGHT", 700);
			}
		}
		
		foreach ($user_solution as $solution)
		{				
				
				if ($user_solution[0]["value2"])
				{
					$content = file_get_contents ( $user_solution[0]["value2"]);

					//merge background and drawing if backgroundimage available
					if( $this->object->getImageFilename() )
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
						if ($this->object->getRadioOption() == "radioOwnSize")
						{
							$resized=imagecreatetruecolor($this->object->getCanvasWidth(),$this->object->getCanvasHeight());
							imagecopyresampled($resized,$background,0,0,0,0,$this->object->getCanvasWidth(),$this->object->getCanvasHeight(),$x1,$y1);
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
				}
				$template->setVariable("SOLUTION", ilUtil::prepareFormOutput($base64));		
		}		

		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($output, TRUE));
		
		if ($result_output)
		{
			$points = $this->object->getMaximumPoints();
			$resulttext = ($points == 1) ? "(%s " . "point" . ")" : "(%s " . "points" . ")"; 
			$template->setCurrentBlock("result_output");
			$template->setVariable("RESULT_OUTPUT", sprintf($resulttext, $points));
			$template->parseCurrentBlock();
		}			
		
		// generate the question output
		$solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html",TRUE, TRUE, "Modules/TestQuestionPool");
		$questionoutput = $template->get();

		$feedback = ($show_feedback) ? $this->getGenericFeedbackOutput($active_id, $pass) : "";
		if (strlen($feedback)) $solutiontemplate->setVariable("FEEDBACK", $this->object->prepareTextareaOutput( $feedback, true ));
		
		$solutiontemplate->setVariable("SOLUTION_OUTPUT", $questionoutput);

		$solutionoutput = $solutiontemplate->get(); 
		
		if(!$show_question_only)
		{
			// get page object output
			$solutionoutput = $this->getILIASPage($solutionoutput);
		}
		
		return $solutionoutput;
	}

	/**
	* Saves the feedback for the question
	*
	* @access public
	*/
	function saveFeedback()
	{		
		include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
		$this->object->saveFeedbackGeneric(0, $_POST["feedback_incomplete"]);
		$this->object->saveFeedbackGeneric(1, $_POST["feedback_complete"]);
		$this->object->cleanupMediaObjectUsage();
		parent::saveFeedback();
	}

	/**	
	 * Sets the ILIAS tabs for this question type
	 * @access public
	 */
	function setQuestionTabs()
	{
		global $rbacsystem, $ilTabs;

		$this->ctrl->setParameterByClass("ilAssQuestionPageGUI", "q_id", $_GET["q_id"]);
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		$q_type = $this->object->getQuestionType();

		if(strlen($q_type))
		{
			$classname = $q_type . "GUI";
			$this->ctrl->setParameterByClass(strtolower($classname), "sel_question_types", $q_type);
			$this->ctrl->setParameterByClass(strtolower($classname), "q_id", $_GET["q_id"]);
		}

		if($_GET["q_id"])
		{
			if($rbacsystem->checkAccess('write', $_GET["ref_id"]))
			{
				// edit page
				$ilTabs->addTarget("edit_content",
					$this->ctrl->getLinkTargetByClass("ilAssQuestionPageGUI", "edit"),
					array("edit", "insert", "exec_pg"),
					"", "", $force_active);
			}

			// edit page
			$ilTabs->addTarget("preview",
				$this->ctrl->getLinkTargetByClass("ilAssQuestionPageGUI", "preview"),
				array("preview"),
				"ilAssQuestionPageGUI", "", $force_active);
		}

		$force_active = false;
		if($rbacsystem->checkAccess('write', $_GET["ref_id"]))
		{
			$url = "";
		
			if($classname) $url = $this->ctrl->getLinkTargetByClass($classname, "editQuestion");
			$commands = $_POST["cmd"];
			if(is_array($commands))
			{
				foreach($commands as $key => $value)
				{
					if(preg_match("/^suggestrange_.*/", $key, $matches))
					{
						$force_active = true;
					}
				}
			}
			// edit question properties
			$ilTabs->addTarget("edit_properties",
				$url,
				array(
					"editQuestion", "save", "cancel", "addSuggestedSolution",
					"cancelExplorer", "linkChilds", "removeSuggestedSolution",
					"saveEdit", "suggestRange"
				),
				$classname, "", $force_active);						
		}
		/*
		if($_GET["q_id"])
		{
			$ilTabs->addTarget("feedback",
				$this->ctrl->getLinkTargetByClass($classname, "feedback"),
				array("feedback", "saveFeedback"),
				$classname, "");
		}
		*/
		// add tab for question feedback within common class assQuestionGUI
        $this->addTab_QuestionFeedback($ilTabs);
		// add tab for question hint within common class assQuestionGUI
		$this->addTab_QuestionHints($ilTabs);

		if ($_GET["q_id"])
		{
			$ilTabs->addTarget("solution_hint",
				$this->ctrl->getLinkTargetByClass($classname, "suggestedsolution"),
				array("suggestedsolution", "saveSuggestedSolution", "outSolutionExplorer", "cancel", 
				"addSuggestedSolution","cancelExplorer", "linkChilds", "removeSuggestedSolution"
				),
				$classname, 
				""
			);
		}

		// Assessment of questions sub menu entry
		if($_GET["q_id"])
		{
			$ilTabs->addTarget("statistics",
				$this->ctrl->getLinkTargetByClass($classname, "assessment"),
				array("assessment"),
				$classname, "");
		}

		if(($_GET["calling_test"] > 0) || ($_GET["test_ref_id"] > 0))
		{
			$ref_id = $_GET["calling_test"];
			if(strlen($ref_id) == 0) $ref_id = $_GET["test_ref_id"];
			$ilTabs->setBackTarget($this->lng->txt("backtocallingtest"), "ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=$ref_id");
		}
		else
		{
			$ilTabs->setBackTarget($this->lng->txt("qpl"), $this->ctrl->getLinkTargetByClass("ilobjquestionpoolgui", "questions"));
		}
	}	
	
	/**
	 * Returns the answer specific feedback for the question
	 * 
	 * @param integer $active_id Active ID of the user
	 * @param integer $pass Active pass
	 * @return string HTML Code with the answer specific feedback
	 * @access public
	 */
	public function getSpecificFeedbackOutput($usersolution)
	{
		return "";
	}
}
?>
