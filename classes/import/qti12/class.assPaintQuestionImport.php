<?php

include_once "./Modules/TestQuestionPool/classes/import/qti12/class.assQuestionImport.php";

/**
* Class for PaintQuestion import
*
* @author Yves Annanias <yves.annanias@llz.uni-halle.de>
* @author Christoph Jobst <cjobst@wifa.uni-leipzig.de>
* @version	$Id: $
* @ingroup 	ModulesTestQuestionPool
*/
class assPaintQuestionImport extends assQuestionImport
{
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
    function fromXML(&$item, $questionpool_id, &$tst_id, &$tst_object, &$question_counter, $import_mapping, array $solutionhints = []) : array
    {
		global $ilUser;

		// empty session variable for imported xhtml mobs
		unset($_SESSION["import_mob_xhtml"]);
		$presentation = $item->getPresentation(); 
		$duration = $item->getDuration();
		$now = getdate();
		$created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);

			// get the generic feedbach
		$feedbacksgeneric = array();
		if (isset($item->itemfeedback))
		{
			foreach ($item->itemfeedback as $ifb)
			{
				if (strcmp($ifb->getIdent(), "response_allcorrect") == 0)
				{
					// found a feedback for the identifier
					if (count($ifb->material))
					{
						foreach ($ifb->material as $material)
						{
							$feedbacksgeneric[1] = $material;
						}
					}
					if ((count($ifb->flow_mat) > 0))
					{
						foreach ($ifb->flow_mat as $fmat)
						{
							if (count($fmat->material))
							{
								foreach ($fmat->material as $material)
								{
									$feedbacksgeneric[1] = $material;
								}
							}
						}
					}
				}
				else if (strcmp($ifb->getIdent(), "response_onenotcorrect") == 0)
				{
					// found a feedback for the identifier
					if (count($ifb->material))
					{
						foreach ($ifb->material as $material)
						{
							$feedbacksgeneric[0] = $material;
						}
					}
					if ((count($ifb->flow_mat) > 0))
					{
						foreach ($ifb->flow_mat as $fmat)
						{
							if (count($fmat->material))
							{
								foreach ($fmat->material as $material)
								{
									$feedbacksgeneric[0] = $material;
								}
							}
						}
					}
				}
			}
		}
		
		$this->object->setTitle($item->getTitle());
		$this->object->setComment($item->getComment());
		$this->object->setAuthor($item->getAuthor());
		$this->object->setOwner($ilUser->getId());
		$this->object->setQuestion($this->object->QTIMaterialToString($item->getQuestiontext()));
		$this->object->setObjId($questionpool_id);
		//$this->object->setEstimatedWorkingTime($duration["h"], $duration["m"], $duration["s"]);		
		$this->object->setPoints($item->getMetadataEntry("POINTS"));
		$this->object->setLineValue($item->getMetadataEntry("allowDifferentLineSize"));
		$this->object->setColorValue($item->getMetadataEntry("allowDifferentColors"));
		$this->object->setResizedImageStatus(0);
		$this->object->setLogCount($item->getMetadataEntry("logCount"));
		$this->object->setLogBkgr($item->getMetadataEntry("logBkgr"));
		
		// additional content editing mode information
		$this->object->setAdditionalContentEditingMode(
			$this->fetchAdditionalContentEditingModeInformation($item)
		);

		// first save the question to get a new question id
		$this->object->saveToDb();


		// convert the generic feedback
		foreach ($feedbacksgeneric as $correctness => $material)
		{
			$m = $this->object->QTIMaterialToString($material);
			$feedbacksgeneric[$correctness] = $m;
		}
		
		// handle the import of media objects in XHTML code
		$questiontext = $this->object->getQuestion();
		if (is_array($_SESSION["import_mob_xhtml"]))
		{
			include_once "./Services/MediaObjects/classes/class.ilObjMediaObject.php";
			include_once "./Services/RTE/classes/class.ilRTE.php";
			foreach ($_SESSION["import_mob_xhtml"] as $mob)
			{
				if ($tst_id > 0)
				{
					$importfile = $this->getTstImportArchivDirectory() . '/' . $mob["uri"];
				}
				else
				{
					$importfile = $this->getQplImportArchivDirectory() . '/' . $mob["uri"];
				}
				global $ilLog;
				$ilLog->write($importfile);
		
				$media_object =& ilObjMediaObject::_saveTempFileAsMediaObject(basename($importfile), $importfile, FALSE);
				ilObjMediaObject::_saveUsage($media_object->getId(), "qpl:html", $this->object->getId());
		
				// images in question text
				$questiontext = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $questiontext);
		
				// images in feedback
				foreach ($feedbacksgeneric as $correctness => $material)
				{
					$feedbacksgeneric[$correctness] = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $material);
				}
			}
		}
		
		$this->object->setQuestion(ilRTE::_replaceMediaObjectImageSrc($questiontext, 1));
		foreach ($feedbacksgeneric as $correctness => $material)
		{
			$this->object->feedbackOBJ->importGenericFeedback(
					$this->object->getId(), $correctness, ilRTE::_replaceMediaObjectImageSrc($material, 1)
			);
		}
		
		// backgroundImage		
		if ($item->getMetadataEntry("backgroundimage") )
		{
			$questionimage = array(
				"imagetype" => $item->getMetadataEntry("imagetype"),
				"label" => $item->getMetadataEntry("imagelabel"),
				"content" => $item->getMetadataEntry("backgroundimage")
			);
			
			$this->object->setImageFilename($questionimage["label"]);			
			$image =& base64_decode($questionimage["content"]);
			$imagepath = $this->object->getImagePath();
			if (!file_exists($imagepath))
			{
				include_once "./Services/Utilities/classes/class.ilUtil.php";
				ilFileUtils::makeDirParents($imagepath);
			}
			$imagepath .=  $questionimage["label"];
			$fh = fopen($imagepath, "wb");
			if ($fh == false)
			{
				//global $ilErr;
				//$ilErr->raiseError($this->object->lng->txt("error_save_image_file") . ": $php_errormsg", $ilErr->MESSAGE);
				//return;
			}
			else
			{
				$imagefile = fwrite($fh, $image);
				fclose($fh);
			}
		}
		
		if ($item->getMetadataEntry("radiooption") == "radioOwnSize") {
			if ($item->getMetadataEntry("resizedbackgroundimage") )
			{
				$resizedquestionimage = array(
						"label" => $item->getMetadataEntry("resizedimagelabel"),
						"content" => $item->getMetadataEntry("resizedbackgroundimage")
				);
				
				$image =& base64_decode($resizedquestionimage["content"]);
				$imagepath = $this->object->getImagePath();
				$imagepath .=  $resizedquestionimage["label"];
				$fh = fopen($imagepath, "wb");
				if ($fh == false)
				{
					//global $ilErr;
					//$ilErr->raiseError($this->object->lng->txt("error_save_image_file") . ": $php_errormsg", $ilErr->MESSAGE);
					//return;
				}
				else
				{
					$imagefile = fwrite($fh, $image);
					fclose($fh);
				}
				$this->object->setResizedImageStatus(1);
				
			} 
			
			//old question with own size but no resized image?
			if ($item->getMetadataEntry("backgroundimage") && empty($item->getMetadataEntry("resizedbackgroundimage"))) 
			{
				$this->object->resizeImage($item->getMetadataEntry("canvasheight"), $item->getMetadataEntry("canvaswidth"));
				$this->object->setResizedImageStatus(1);
			}
		}

		// Sample solution
		if ($item->getMetadataEntry("imagebestsolution") )
		{
		    $questionimage = array(
		        "imagetype" => $item->getMetadataEntry("imagetypebestsolution"),
		        "label" => $item->getMetadataEntry("imagelabelbestsolution"),
		        "content" => $item->getMetadataEntry("imagebestsolution")
		    );
		    
		    $this->object->setImageFilenameBestsolution($questionimage["label"]);
		    $image =& base64_decode($questionimage["content"]);
		    $imagepath = $this->object->getImagePath();
		    if (!file_exists($imagepath))
		    {
		        include_once "./Services/Utilities/classes/class.ilUtil.php";
		        ilFileUtils::makeDirParents($imagepath);
		    }
		    $imagepath .= $this->object->getImageFilenameBestsolution();
		    $fh = fopen($imagepath, "wb");
		    if ($fh == false)
		    {
		        //global $ilErr;
		        //$ilErr->raiseError($this->object->lng->txt("error_save_image_file") . ": $php_errormsg", $ilErr->MESSAGE);
		        //return;
		    }
		    else
		    {
		        $imagefile = fwrite($fh, $image);
		        fclose($fh);
		    }
		}
		
		$this->object->setRadioOption($item->getMetadataEntry("radiooption"));
		$this->object->setCanvasHeight($item->getMetadataEntry("canvasheight"));
		$this->object->setCanvasWidth($item->getMetadataEntry("canvaswidth"));
			
		// Now save the question again
		$this->object->saveToDb();
		
		// Save solutionhints
		foreach ($solutionhints as $hint) {
		    $h = new ilAssQuestionHint();
		    $h->setQuestionId($this->object->getId());
		    $h->setIndex($hint['index']);
		    $h->setPoints($hint['points']);
		    $h->setText($hint['txt']);
		    $h->save();
		}
		
		
		// import mapping for tests
		if ($tst_id > 0)
		{
			$q_1_id = $this->object->getId();
			$question_id = $this->object->duplicate(true, null, null, null, $tst_id);
			$tst_object->questions[$question_counter++] = $question_id;
			$import_mapping[$item->getIdent()] = array("pool" => $q_1_id, "test" => $question_id);
		}
		else
		{
			$import_mapping[$item->getIdent()] = array("pool" => $this->object->getId(), "test" => 0);
		}
		
		return $import_mapping;
	}
}

?>