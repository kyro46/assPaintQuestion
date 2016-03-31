<?php

include_once "./Modules/TestQuestionPool/classes/import/qti12/class.assQuestionImport.php";

/**
* Class for PaintQuestion import
*
* @author Yves Annanias <yves.annanias@llz.uni-halle.de>
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
	function fromXML(&$item, $questionpool_id, &$tst_id, &$tst_object, &$question_counter, &$import_mapping)
	{
		global $ilUser;

		// empty session variable for imported xhtml mobs
		unset($_SESSION["import_mob_xhtml"]);
		$presentation = $item->getPresentation(); 
		$duration = $item->getDuration();
		$now = getdate();
		$created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);

		$feedbacksgeneric = array();

		$this->object->setTitle($item->getTitle());
		$this->object->setComment($item->getComment());
		$this->object->setAuthor($item->getAuthor());
		$this->object->setOwner($ilUser->getId());
		$this->object->setQuestion($this->object->QTIMaterialToString($item->getQuestiontext()));
		$this->object->setObjId($questionpool_id);
		$this->object->setEstimatedWorkingTime($duration["h"], $duration["m"], $duration["s"]);		
		$this->object->setPoints($item->getMetadataEntry("points"));
		$this->object->setLineValue($item->getMetadataEntry("allowDifferentLineSize"));
		$this->object->setColorValue($item->getMetadataEntry("allowDifferentColors"));
		
		$this->object->saveToDb('', false);

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
					include_once "./Modules/Test/classes/class.ilObjTest.php";
					$importfile = ilObjTest::_getImportDirectory() . '/' . $mob["uri"];
				}
				else
				{
					include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
					$importfile = ilObjQuestionPool::_getImportDirectory() . '/' . $mob["uri"];
				}
				$media_object =& ilObjMediaObject::_saveTempFileAsMediaObject(basename($importfile), $importfile, FALSE);
				ilObjMediaObject::_saveUsage($media_object->getId(), "qpl:html", $this->object->getId());
				$questiontext = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $questiontext);			
			}
		}
		$this->object->setQuestion(ilRTE::_replaceMediaObjectImageSrc($questiontext, 1));
		// feedback
		$feedbacksgeneric = array();		
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
			
		// genericFeedback
		foreach ($feedbacksgeneric as $correctness => $material)
		{			
			$m = $this->object->QTIMaterialToString($material);
			$feedbacksgeneric[$correctness] = $m;			
		}
		foreach ($feedbacksgeneric as $correctness => $material)
		{
			$this->object->saveFeedbackGeneric($correctness, ilRTE::_replaceMediaObjectImageSrc($material, 1));
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
				ilUtil::makeDirParents($imagepath);
			}
			$imagepath .=  $questionimage["label"];
			$fh = fopen($imagepath, "wb");
			if ($fh == false)
			{
	//									global $ilErr;
	//									$ilErr->raiseError($this->object->lng->txt("error_save_image_file") . ": $php_errormsg", $ilErr->MESSAGE);
	//									return;
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

		$this->object->saveToDb();

		if ($tst_id > 0)
		{
			$q_1_id = $this->object->getId();
			$question_id = $this->object->duplicate(true);
			$tst_object->questions[$question_counter++] = $question_id;
			$import_mapping[$item->getIdent()] = array("pool" => $q_1_id, "test" => $question_id);
		}
		else
		{
			$import_mapping[$item->getIdent()] = array("pool" => $this->object->getId(), "test" => 0);
		}
	}
}

?>
