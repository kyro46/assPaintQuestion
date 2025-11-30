<?php

 /**
 * The assPaintQuestionGUI class encapsulates the GUI representation
 * for Question-Type-Plugin.
 *
 * @author	Christoph Jobst <iliasplugins.christoph.jobst@outlook.de>
 * @ingroup ModulesTestQuestionPool
 * 
 * @ilctrl_iscalledby assPaintQuestionGUI: ilObjQuestionPoolGUI, ilObjTestGUI, ilQuestionEditGUI, ilTestExpressPageObjectGUI
 * @ilctrl_calls assPaintQuestionGUI: ilFormPropertyDispatchGUI
 */
class assPaintQuestionGUI extends assQuestionGUI implements ilGuiQuestionScoringAdjustable
{	 
    /**
    * @const	string	URL base path for including special javascript and css files
    */
    const URL_PATH = "./Customizing/global/plugins/Modules/TestQuestionPool/Questions/assPaintQuestion";

    /**
     * @const	string 	URL suffix to prevent caching of css files (increase with every change)
     * 					Note: this does not yet work with $tpl->addJavascript()
     */
    const URL_SUFFIX = "?css_version=1.5.0";
    
	var $plugin = null;

	public assQuestion $object;
	
	public function getPlugin(): ilPlugin
	{
	    return $this->plugin;
	}
	
	public function setPlugin(ilPlugin $plugin): void
	{
	    $this->plugin = $plugin;
	}
	
	/**
	 * Constructor
	 *
	 * @param integer $id The database id of a question object
	 * @access public
	 */
	public function __construct($id = -1)
	{	 
	    global $tpl;
	    
		parent::__construct();
		
		// init the plugin object
		try {
		    global $DIC;
		    
		    /** @var ilComponentRepository $component_repository */
		    $component_repository = $DIC["component.repository"];
		    
		    $info = null;
		    $plugin_name = 'assPaintQuestion';
		    $info = $component_repository->getPluginByName($plugin_name);
		    
		    /** @var ilComponentFactory $component_factory */
		    $component_factory = $DIC["component.factory"];
		    
		    /** @var ilQuestionsPlugin $plugin_obj */
		    $plugin_obj = $component_factory->getPlugin($info->getId());
		    
		    if (!is_null($info) && $info->isActive()) {
		        $this->setPlugin($plugin_obj);
		    } else {
		        throw new ilPluginException($plugin_name . ' plugin is not active');
		    }
		} catch (ilPluginException $e) {
		    global $tpl;
		    $tpl->setOnScreenMessage('failure', $e->getMessage(), true);
		}

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
	 * @param bool $is_save_cmd
	 * @return bool
	 */
	public function editQuestion(
	    bool $checkonly = false,
	    ?bool $is_save_cmd = null
	    ): bool 
	{
		global $ilDB;					

		$save = $is_save_cmd ?? $this->isSaveCommand();
		$plugin = $this->object->getPlugin();		
		
		$this->getQuestionTemplate();
		$form = new ilPropertyFormGUI();
		$this->editForm = $form;
		
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->plugin->txt("edit_assPaintQuestion"));
		$form->setMultipart(FALSE);
		$form->setTableWidth("100%");
		$form->setId("assPaintQuestion");
		
		// Baseinput: title, author, description, question, working time (assessment mode)		
		$this->addBasicQuestionFormProperties($form);
		
		$this->populateQuestionSpecificFormPart($form);
		$this->populateAnswerSpecificFormPart($form);
		
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
	protected function writePostData($always = false): int
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
	            $file_org_name = $_FILES['imagefile']['name'] ?? '';
	            $file_temp_name = $_FILES['imagefile']['tmp_name'] ?? '';
	            
	            if ($file_temp_name !== '')
	            {
	                $this->object->deleteImage(); //Something (probably new) was uploaded - delete the old image
	                
                    // check suffix
                    $file_name_parts = explode('.', $file_org_name);
                    $suffix = strtolower(array_pop($file_name_parts));
                    if (in_array($suffix, ['jpg', 'jpeg', 'png'])) {
                        // upload image
                        $filename = $this->object->buildHashedImageFilename($file_org_name);
                        if ($this->object->setImageFilename($filename, $file_temp_name) == 0) {
                        }
                    }
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
	            $file_org_name = $_FILES['imagefile_bestsolution']['name'] ?? '';
	            $file_temp_name = $_FILES['imagefile_bestsolution']['tmp_name'] ?? '';
	            
	            if ($file_temp_name !== '')
	            {
	                $this->object->deleteImageBestsolution(); //Something (probably new) was uploaded - delete the old image
	                
	                // check suffix
	                $file_name_parts = explode('.', $file_org_name);
	                $suffix = strtolower(array_pop($file_name_parts));
	                if (in_array($suffix, ['jpg', 'jpeg', 'png'])) {
	                    // upload image
	                    $filename = $this->object->buildHashedImageFilename($file_org_name);
	                    if ($this->object->setImageFilenameBestsolution($filename, $file_temp_name) == 0) {
	                    }
	                }
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
	 * Get the output for question preview
	 * (called from ilObjQuestionPoolGUI)
	 * 
	 * @param boolean	$show_question_only      show only the question instead of embedding page (true/false)
	 * @param boolean	$show_inline_feedback
	 */
	public function getPreview(bool $show_question_only = false, bool $show_inline_feedback = false): string
	{	
	    global $DIC, $tpl;			
		$plugin       = $this->object->getPlugin();		
		$template = new ilTemplate("output_dev.html", true, true, 'public/Customizing/global/plugins/Modules/TestQuestionPool/Questions/assPaintQuestion');
		
		$template->setVariable("QUESTIONTEXT", self::prepareTextareaOutput($this->object->getQuestion(), TRUE));
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

		$DIC->globalScreen()->layout()->meta()->addCss(self::URL_PATH.'/templates/default/_assets/literallycanvas.css'.self::URL_SUFFIX);
		$DIC->globalScreen()->layout()->meta()->addJs(self::URL_PATH.'/templates/default/_js_libs/react-0.14.3.js');
		$DIC->globalScreen()->layout()->meta()->addJs(self::URL_PATH.'/templates/default/_js_libs/literallycanvas.js');
		
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
    * (this function could be private)
    *
    * @param integer $active_id			           The active user id
    * @param integer $pass					           The test pass
    * @param boolean $is_question_postponed           Question is postponed
    * @param boolean $user_post_solutions	           Use post solutions
    * @param boolean $show_specific_inline_feedback   Show a feedback
    * @return string
    */
    public function getTestOutput(
        int $active_id,
        int $pass,
        bool $is_question_postponed = false,
        array|bool $user_post_solutions = false,
        bool $show_specific_inline_feedback = false
        ): string 
    {
	    global $DIC; $tpl;
		// get the solution of the user for the active pass or from the last pass if allowed
		$user_solution = array();
		if ($active_id)
		{
		    $user_solution = $this->object->getSolutionStored($active_id, $pass, true);
			if (!is_array($user_solution)) 
			{
				$user_solution = array();
			}
		}
		
		$plugin       = $this->object->getPlugin();		
		$template = new ilTemplate("output_dev.html", true, true, 'public/Customizing/global/plugins/Modules/TestQuestionPool/Questions/assPaintQuestion');
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
		
		$DIC->globalScreen()->layout()->meta()->addCss(self::URL_PATH.'/templates/default/_assets/literallycanvas.css'.self::URL_SUFFIX);
		$DIC->globalScreen()->layout()->meta()->addJs(self::URL_PATH.'/templates/default/_js_libs/react-0.14.3.js');
		$DIC->globalScreen()->layout()->meta()->addJs(self::URL_PATH.'/templates/default/_js_libs/literallycanvas.js');
		
		// letzte gespeicherte Eingabe anzeigen
		$base64 = "";
		if ($user_solution["value2"])
		{
			// wenn eingabe vorhanden, dann bild von gegebener url als base64-string konvertieren
			$content = file_get_contents ( $user_solution["value2"]);
			$base64 = 'data:image/png;base64,'.base64_encode( $content );
		}							
		
		if ($user_solution["value2"] != 'path'){
		    $template->setVariable("RESUMEJSON",preg_replace("{\\\}", "\\\\\\",(string)$user_solution["value1"]));
		}
		
		$template->setVariable("RESUME", ilLegacyFormElementsUtil::prepareFormOutput($base64));	
		
		$template->setVariable("QUESTIONTEXT", self::prepareTextareaOutput($output, TRUE));	
		$questionoutput = $template->get();
		$pageoutput = $this->outQuestionPage("", $is_question_postponed, $active_id, $questionoutput);
		return $pageoutput;		
	}

	/**
	 * Get the question solution output
	 * @param integer $active_id             The active user id
	 * @param integer $pass                  The test pass
	 * @param boolean $graphicalOutput       Show visual feedback for right/wrong answers
	 * @param boolean $result_output         Show the reached points for parts of the question
	 * @param boolean $show_question_only    Show the question without the ILIAS content around
	 * @param boolean $show_feedback         Show the question feedback
	 * @param boolean $show_correct_solution Show the correct solution instead of the user solution
	 * @param boolean $show_manual_scoring   Show specific information for the manual scoring output
	 * @param bool    $show_question_text
	 * @param bool    $show_inline_feedback
	 * @return string solution output of the question as HTML code
	 */
	function getSolutionOutput(
	    int $active_id,
	    ?int $pass = null,
	    bool $graphical_output = false,
	    bool $result_output = false,
	    bool $show_question_only = true,
	    bool $show_feedback = false,
	    bool $show_correct_solution = false,
	    bool $show_manual_scoring = false,
	    bool $show_question_text = true,
	    bool $show_inline_feedback = true
	    ): string
    {
		global $tpl;
		// get the solution of the user for the active pass or from the last pass if allowed
		$user_solution = array();
		if (($active_id > 0) && (!$show_correct_solution))
		{
			// get the solutions of a user
		    $user_solution = $this->object->getSolutionStored($active_id, $pass, true);
			if (!is_array($user_solution)) 
			{
				$user_solution = array();
			}
		} else {			
			$user_solution = array();
		}

		$plugin       = $this->object->getPlugin();		
		$template = new ilTemplate("solution.html", true, true, 'public/Customizing/global/plugins/Modules/TestQuestionPool/Questions/assPaintQuestion');
		
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
		//preset formular
		$template->setVariable("SOLUTION", ilLegacyFormElementsUtil::prepareFormOutput($base64));

		
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
				
				if ($user_solution["value2"])
				{
					$content = file_get_contents ($user_solution["value2"]);

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
				$template->setVariable("SOLUTION", ilLegacyFormElementsUtil::prepareFormOutput($base64));		
		}		

		$template->setVariable("QUESTIONTEXT", self::prepareTextareaOutput($output, TRUE));
		
		if ($result_output)
		{
			$points = $this->object->getMaximumPoints();
			$resulttext = ($points == 1) ? "(%s " . "point" . ")" : "(%s " . "points" . ")"; 
			$template->setCurrentBlock("result_output");
			$template->setVariable("RESULT_OUTPUT", sprintf($resulttext, $points));
			$template->parseCurrentBlock();
		}			
		
		// generate the question output
		$solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html", true, true, "components/ILIAS/TestQuestionPool");
		
		$questionoutput = $template->get();

		$feedback = ($show_feedback) ? $this->getGenericFeedbackOutput($active_id, $pass) : "";
		if (strlen($feedback)) $solutiontemplate->setVariable("FEEDBACK", self::prepareTextareaOutput( $feedback, true ));
		
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
	 * Returns the answer specific feedback for the question
	 *
	 * @param array $userSolution Array with the user solutions
	 * @return string HTML Code with the answer specific feedback
	 * @access public
	 */
	public function getSpecificFeedbackOutput($userSolution): string
	{
	    // By default no answer specific feedback is defined
	    $output = '';
	    return self::prepareTextareaOutput($output, TRUE);
	}
	
	/**
	 * Sets the ILIAS tabs for this question type
	 * called from ilObjTestGUI and ilObjQuestionPoolGUI
	 */
	public function setQuestionTabs(): void
	{
	    parent::setQuestionTabs();
	}
	
	/**
	 * Adds the question specific forms parts to a question property form gui.
	 */
	public function populateQuestionSpecificFormPart(ilPropertyFormGUI $form): ilPropertyFormGUI
	{
	    $plugin = $this->object->getPlugin();
	    
	    $form->setTitle($this->plugin->txt("edit_assPaintQuestion"));
	    
	    //Start Question specific
	    // points
	    $points = new ilNumberInputGUI($plugin->txt("points"), "points");
	    $points->setSize(3);
	    $points->setMinValue(1);
	    $points->allowDecimals(1);
	    $points->setRequired(true);
	    $points->setValue($this->object->getPoints());
	    $form->addItem($points);

	    return $form;
	}
	
	/**
	 * Extracts the question specific values from the request and applies them
	 * to the data object.
	 */
	public function writeQuestionSpecificPostData(ilPropertyFormGUI $form): void
	{
	    $this->object->setPoints($this->request_data_collector->float('points'));
	}
	
	/**
	 * Returns a list of postvars which will be suppressed in the form output when used in scoring adjustment.
	 * The form elements will be shown disabled, so the users see the usual form but can only edit the settings, which
	 * make sense in the given context.
	 *
	 * E.g. array('cloze_type', 'image_filename')
	 *
	 * @return string[]
	 */
	public function getAfterParticipationSuppressionQuestionPostVars(): array
	{
	    return [];
	}
	
	public function populateAnswerSpecificFormPart(\ilPropertyFormGUI $form): ilPropertyFormGUI
	{
	    $plugin = $this->object->getPlugin();
	    
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
	    
	    return $form;
	}
	
	public function writeAnswerSpecificPostData(ilPropertyFormGUI $form): void
	{
	    #not needed for Paint
	}
	
	/**
	 * Returns a list of postvars which will be suppressed in the form output when used in scoring adjustment.
	 * The form elements will be shown disabled, so the users see the usual form but can only edit the settings, which
	 * make sense in the given context.
	 *
	 * E.g. array('cloze_type', 'image_filename')
	 *
	 * @return string[]
	 */
	public function getAfterParticipationSuppressionAnswerPostVars(): array
	{
	    return [];
	}
	
	public function populateCorrectionsFormProperties(ilPropertyFormGUI $form): void
	{
	    $this->populateQuestionSpecificFormPart($form);
	}
	
	/**
	 * @param ilPropertyFormGUI $form
	 */
	public function saveCorrectionsFormProperties(ilPropertyFormGUI $form): void
	{
	    $this->object->setPoints((float) str_replace(',', '.', $form->getInput('points')));
	    // TODO let user change more inputs?
	}
	
	public function prepareReprintableCorrectionsForm(ilPropertyFormGUI $form): void
	{
	    #not needed for Paint
	}
}
?>
